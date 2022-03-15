<?php
/**
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 */

namespace Wikimedia\Equivset\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use UtfNormal\Utils;

/**
 * Generate Equivset Command.
 */
class GenerateEquivset extends Command {

	/**
	 * @var string
	 */
	protected $dataDir;

	/**
	 * @var string
	 */
	protected $distDir;

	/**
	 * Generate Equivset
	 *
	 * @param string $dataDir Data Directory
	 * @param string $distDir Distribution Directory
	 */
	public function __construct( $dataDir = '', $distDir = '' ) {
		parent::__construct();

		$this->dataDir = $dataDir ?: __DIR__ . '/../../data';
		$this->distDir = $distDir ?: __DIR__ . '/../../dist';
	}

	/**
	 * {@inheritdoc}
	 */
	protected function configure() {
		$this->setName( 'generate-equivset' );
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param InputInterface $input Input.
	 * @param OutputInterface $output Output.
	 *
	 * @return int Return status.
	 */
	public function execute( InputInterface $input, OutputInterface $output ) {
		$lines = file( $this->dataDir . '/equivset.in' );
		if ( !$lines ) {
			throw new \Exception( "Unable to open equivset.in" );
		}

		# \s matches \xa0 in non-unicode mode, which is not what we want
		# So we need to make our own whitespace class
		$sp = '[\ \t]';

		$lineNum = 0;
		$setsByChar = [];
		$sets = [];
		$exitStatus = 0;

		foreach ( $lines as $index => $line ) {
			$lineNum = $index + 1;
			# Whether the line ends with a nul character
			$mapToEmpty = ( strpos( $line, "\0" ) === strlen( $line ) - 2 );

			$line = trim( $line );

			# Filter comments
			if ( !$line || $line[0] == '#' ) {
				continue;
			}

			# Process line
			if ( !preg_match(
				"/^(?P<hexleft> [A-F0-9]+) $sp+ (?P<charleft> .+?) $sp+ => $sp+ " .
					"(?:(?P<hexright> [A-F0-9]+) $sp+|) (?P<charright> .+?) $sp* (?: \#.*|) $ /x",
					$line, $m
				)
			) {
				$output->writeln( "<error>Error: invalid entry at line $lineNum: $line</error>" );
				$exitStatus = 1;
				continue;
			}
			$error = false;

			if ( Utils::codepointToUtf8( hexdec( $m['hexleft'] ) ) != $m['charleft'] ) {
				$actual = Utils::utf8ToCodepoint( $m['charleft'] );
				if ( $actual === false ) {
					$output->writeln( "Bytes: " . strlen( $m['charleft'] ) );
					$output->writeln( bin2hex( $line ) );
					$hexForm = bin2hex( $m['charleft'] );
					$output->writeln( "<error>Invalid UTF-8 character \"{$m['charleft']}\" ($hexForm) at " .
						"line $lineNum: $line</error>" );
				} else {
					$output->writeln( "<error>Error: left number ({$m['hexleft']}) does not match left " .
						"character ($actual) at line $lineNum: $line</error>" );
				}
				$error = true;
			}
			if ( !empty( $m['hexright'] )
				&& Utils::codepointToUtf8( hexdec( $m['hexright'] ) ) != $m['charright']
			) {
				$actual = Utils::utf8ToCodepoint( $m['charright'] );
				if ( $actual === false ) {
					$hexForm = bin2hex( $m['charright'] );
					$output->writeln( "<error>Invalid UTF-8 character \"{$m['charleft']}\" ($hexForm) at " .
						"line $lineNum: $line</error>" );
				} else {
					$output->writeln( "<error>Error: right number ({$m['hexright']}) does not match right " .
						"character ($actual) at line $lineNum: $line</error>" );
				}
				$error = true;
			}
			if ( $error ) {
				$exitStatus = 1;
				continue;
			}
			if ( $mapToEmpty || $m['charright'] == 'NUL' ) {
				$m['charright'] = '';
			}

			# Find the set for the right character, add a new one if necessary
			if ( isset( $setsByChar[$m['charright']] ) ) {
				$setName = $setsByChar[$m['charright']];
				$setsByChar[$m['charleft']] = $setsByChar[$m['charright']];
			} else {
				$setName = $m['charright'];
				$setsByChar[$m['charleft']] = $m['charright'];
			}

			if ( !isset( $sets[$setName] ) ) {
				$sets[$setName] = [ $setName ];
			}

			$sets[$setName][] = $m['charleft'];
		}

		$jsonData = [
			'_readme' => [
				'This file is generated by `bin/console generate-equivset`',
				'It contains a map of characters, encoded in UTF-8, such that running',
				'strtr() on a string with this map will cause confusable characters to',
				'be reduced to a canonical representation. The same array is also',
				'available in serialized form, in equivset.ser.',
			],
		];

		// JSON
		$data = json_encode(
			$jsonData + $setsByChar,
			JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
		);
		file_put_contents( $this->distDir . '/equivset.json', $data );

		// Serialized.
		file_put_contents( $this->distDir . '/equivset.ser', serialize( $setsByChar ) );

		// Text File.
		touch( $this->distDir . '/equivset.txt' );
		$textFile = fopen( $this->distDir . '/equivset.txt', 'w' );
		foreach ( $sets as $members ) {
			fwrite( $textFile, implode( ' ', $members ) . PHP_EOL );
		}
		fclose( $textFile );

		if ( $exitStatus > 0 ) {
			$output->writeln( '<error>Finished with errors</error>' );
		} else {
			$output->writeln( '<info>Finished</info>' );
		}

		return $exitStatus;
	}
}
