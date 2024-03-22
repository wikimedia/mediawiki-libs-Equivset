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

use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generate Equivset Command.
 */
class GenerateEquivset extends Command {

	/**
	 * @var string
	 */
	protected string $dataDir;

	/**
	 * @var string
	 */
	protected string $distDir;

	/**
	 * Generate Equivset
	 *
	 * @param string $dataDir Data Directory
	 * @param string $distDir Distribution Directory
	 */
	public function __construct( string $dataDir = '', string $distDir = '' ) {
		parent::__construct();

		$this->dataDir = $dataDir ?: __DIR__ . '/../../data';
		$this->distDir = $distDir ?: __DIR__ . '/../../dist';
	}

	/**
	 * {@inheritdoc}
	 */
	protected function configure() {
		$this->setName( 'generate-equivset' );
		$this->setDescription(
			'Generate the JSON, PHP, and plain text versions of the equivset in `./dist`'
		);
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
		// phpcs:ignore Generic.PHP.NoSilencedErrors
		$fp = @fopen( $this->dataDir . '/equivset.in', 'rb' );
		if ( $fp === false ) {
			throw new RuntimeException( "Unable to open equivset.in" );
		}

		$lineNum = 0;
		$setsByChar = [];
		$sets = [];
		$exitStatus = 0;
		$lastChar = null;

		// phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition.FoundInWhileCondition
		while ( ( $line = fgets( $fp ) ) !== false ) {
			$lineNum++;
			$line = trim( $line );

			# Filter comments
			if ( !$line || $line[0] == '#' ) {
				continue;
			}

			# Process line
			if ( !preg_match(
				'/^(?P<hexleft>[0-9A-F]+) +(?P<charleft>.) +=> +' .
					'(?:(?P<hexright>[0-9A-F]+) +(?P<charright>.)|(?P<invisible>invisible)|(?P<space>space))$/u',
					$line, $m, PREG_UNMATCHED_AS_NULL
				)
			) {
				$output->writeln( "<error>Error: invalid entry at line $lineNum: $line</error>" );
				$exitStatus = 1;
				continue;
			}
			$error = false;

			if ( mb_chr( hexdec( $m['hexleft'] ) ) !== $m['charleft'] ) {
				$actual = strtoupper( dechex( mb_ord( $m['charleft'] ) ) );
				$output->writeln( "<error>Error: left number ({$m['hexleft']}) does not match left " .
					"character ($actual) at line $lineNum: $line</error>" );
				$error = true;
			}
			if ( isset( $m['invisible'] ) ) {
				$m['charright'] = '';
			} elseif ( isset( $m['space'] ) ) {
				$m['charright'] = ' ';
			} elseif ( mb_chr( hexdec( $m['hexright'] ) ) !== $m['charright'] ) {
				$actual = strtoupper( dechex( mb_ord( $m['charright'] ) ) );
				$output->writeln( "<error>Error: right number ({$m['hexright']}) does not match right " .
					"character ($actual) at line $lineNum: $line</error>" );
				$error = true;
			}
			if ( $m['charleft'] === $m['charright'] ) {
				$output->writeln( "<error>Error: {$m['hexright']} maps to itself</error>" );
				$error = true;
			}
			if ( isset( $setsByChar[$m['charleft']] ) ) {
				$output->writeln( "<error>Error: Duplicate character ({$m['charleft']}) " .
					"at line $lineNum: $line</error>" );
				$error = true;
			}
			if ( $lastChar !== null && $m['charleft'] < $lastChar ) {
				$output->writeln( "<error>Error: Characters not in order based on hex-value ({$m['charleft']}) " .
					"at line $lineNum: $line</error>" );
				$error = true;
			} else {
				$lastChar = $m['charleft'];
			}
			if ( $error ) {
				$exitStatus = 1;
				continue;
			}

			# Find the set for the right character, add a new one if necessary
			$setName = $setsByChar[$m['charright']] ?? $m['charright'];

			// Circle detected, one edge in every circle is redundant and can just be ignored
			if ( $setName === $m['charleft'] ) {
				continue;
			}

			$sets[$setName] ??= [ $setName ];

			// When a mapping between two chars exists before one of them gets the final set, a merge is needed
			if ( isset( $sets[$m['charleft']] ) ) {
				foreach ( $sets[$m['charleft']] as $char ) {
					$setsByChar[$char] = $setName;
					$sets[$setName][] = $char;
				}
				unset( $sets[$m['charleft']] );
			} else {
				$setsByChar[$m['charleft']] = $setName;
				$sets[$setName][] = $m['charleft'];
			}
		}

		$header = [
			'This file is generated by `bin/console generate-equivset`',
			'It contains a map of characters, encoded in UTF-8, such that running',
			'strtr() on a string with this map will cause confusable characters to',
			'be reduced to a canonical representation.',
		];

		// JSON
		$data = json_encode(
			[ '_readme' => implode( ' ', $header ) ] + $setsByChar,
			JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
		);
		$data = preg_replace( '/^ +/m', "\t", $data );
		file_put_contents( $this->distDir . '/equivset.json', $data );

		// PHP file
		file_put_contents( $this->distDir . '/equivset.php', self::generatePHP( $setsByChar, $header ) );

		// Text File.
		uksort( $sets, [ self::class, 'compareCodePoints' ] );
		touch( $this->distDir . '/equivset.txt' );
		$textFile = fopen( $this->distDir . '/equivset.txt', 'w' );
		foreach ( $sets as $members ) {
			$setName = array_shift( $members );
			usort( $members, [ self::class, 'compareCodePoints' ] );
			fwrite( $textFile, $setName . ' ' . implode( ' ', $members ) . "\n" );
		}
		fclose( $textFile );

		if ( $exitStatus > 0 ) {
			$output->writeln( '<error>Finished with errors</error>' );
		} else {
			$output->writeln( '<info>Finished</info>' );
		}

		return $exitStatus;
	}

	/**
	 * @param string $a
	 * @param string $b
	 * @return int
	 */
	private static function compareCodePoints( string $a, string $b ) {
		if ( $a === '' ) {
			return -1;
		} elseif ( $b === '' ) {
			return 1;
		}
		return mb_ord( $a ) - mb_ord( $b );
	}

	/**
	 * @param string[] $data
	 * @param string[] $header
	 * @return string
	 */
	private static function generatePHP( array $data, array $header ): string {
		$s = "<?php\n"
			. "// " . implode( "\n// ", $header ) . "\n"
			. "return [\n";
		foreach ( $data as $key => $value ) {
			$s .= "\t" . var_export( (string)$key, true ) . ' => ' . var_export( $value, true ) . ",\n";
		}
		$s .= "];\n";
		return $s;
	}
}
