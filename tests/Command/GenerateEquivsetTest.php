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

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generate Equivset Command.
 *
 * @covers \Wikimedia\Equivset\Command\GenerateEquivset
 */
class GenerateEquivsetTest extends TestCase {

	/**
	 * Test Configuration
	 */
	public function testConfigure() {
		$command = new GenerateEquivset();

		$this->assertEquals( 'generate-equivset', $command->getName() );
	}

	/**
	 * Test regenerated files
	 */
	public function testRegeneratedDist() {
		// Define a temp storage for the regenerated files
		[ , $dist ] = $this->mockFileSystem();

		// Run generate command
		$command = new GenerateEquivset( '', $dist->url() );
		$input = $this->createMock( InputInterface::class );
		$output = $this->createMock( OutputInterface::class );
		$status = $command->execute( $input, $output );

		// Compare the regenerated result against the current files
		$this->assertSame( 0, $status );
		foreach ( [
			'equivset.json',
			'equivset.php',
			'equivset.txt',
		] as $filename ) {
			$this->assertSame(
				file_get_contents( __DIR__ . '/../../dist/' . $filename ),
				$dist->getChild( $filename )->getContent(),
				$filename . ' does not match equivset.in, run "composer generate"'
			);
		}
	}

	/**
	 * Test Mocked Execute.
	 */
	public function testExecute() {
		$in = "# Testing...\n30 0 => 4F O";

		[ $data, $dist ] = $this->mockFileSystem( $in );

		$command = new GenerateEquivset( $data->url(), $dist->url() );

		$input = $this->createMock( InputInterface::class );
		$output = $this->createMock( OutputInterface::class );

		$status = $command->execute( $input, $output );

		$this->assertSame( 0, $status );

		$this->assertTrue( $dist->hasChild( 'equivset.php' ) );
		$this->assertTrue( $dist->hasChild( 'equivset.json' ) );
		$this->assertTrue( $dist->hasChild( 'equivset.txt' ) );
	}

	/**
	 * Test Live Execute.
	 */
	public function testLiveExecute() {
		// Write the output to memory.
		[ , $dist ] = $this->mockFileSystem();

		$command = new GenerateEquivset( '', $dist->url() );

		$input = $this->createMock( InputInterface::class );
		$output = $this->createMock( OutputInterface::class );

		$status = $command->execute( $input, $output );

		$this->assertSame( 0, $status );

		$this->assertTrue( $dist->hasChild( 'equivset.php' ) );
		$this->assertTrue( $dist->hasChild( 'equivset.json' ) );
		$this->assertTrue( $dist->hasChild( 'equivset.txt' ) );

		$output = require $dist->getChild( 'equivset.php' )->url();

		$this->assertEquals( 'O', $output[0] );
	}

	/**
	 * Test Execute Fail Open
	 */
	public function testExecuteFailOpen() {
		[ $data, $dist ] = $this->mockFileSystem();

		$command = new GenerateEquivset( $data->url(), $dist->url() );

		$input = $this->createMock( InputInterface::class );
		$output = $this->createMock( OutputInterface::class );

		$this->expectException( \Exception::class );
		$this->expectExceptionMessage( 'Unable to open equivset.in' );
		$command->execute( $input, $output );
	}

	/**
	 * Test Execute Fail Malformed
	 *
	 * Ensure that malformed input data results in a failure of the
	 * generate-equivset command.
	 */
	public function testExecuteFailMalformed() {
		$in = "0 => 4F O";
		$out = [
			0 => 'O',
		];

		[ $data, $dist ] = $this->mockFileSystem( $in );

		$command = new GenerateEquivset( $data->url(), $dist->url() );

		$input = $this->createMock( InputInterface::class );
		$output = $this->createMock( OutputInterface::class );
		$output->method( 'writeln' )
			->withConsecutive(
				[ $this->stringContains( 'Error: invalid entry' ) ],
				[ $this->stringContains( 'Finished with errors' ) ]
			);
		$status = $command->execute( $input, $output );

		$this->assertSame( 1, $status );

		$this->assertNotEquals( $out, require $dist->getChild( 'equivset.php' )->url() );
	}

	/**
	 * Provide Not Matching Code Points.
	 */
	public function provideNotMatchingCodePoints() {
		return [
			[ 'left', '31', '31 0 => 4F O' ],
			[ 'right', '4', '30 0 => 4 O' ],
		];
	}

	/**
	 * Test Execute Fail Not Matching Codepoint
	 *
	 * Ensure that code points and letters not matching results in a failure of
	 * the generate-equivset command.
	 *
	 * @param string $side The left or right side
	 * @param string $number N being used
	 * @param string $in Equivset line
	 *
	 * @dataProvider provideNotMatchingCodePoints
	 */
	public function testExecuteFailNotMatchingCodepoint( string $side, string $number, string $in ) {
		$out = [
			0 => 'O',
		];

		[ $data, $dist ] = $this->mockFileSystem( $in );

		$command = new GenerateEquivset( $data->url(), $dist->url() );

		$input = $this->createMock( InputInterface::class );
		$output = $this->createMock( OutputInterface::class );
		$output->method( 'writeln' )
			->withConsecutive(
				[ $this->stringContains( "Error: $side number ($number) does not match" ) ],
				[ $this->stringContains( 'Finished with errors' ) ]
			);

		$status = $command->execute( $input, $output );

		$this->assertSame( 1, $status );

		$this->assertNotEquals( $out, require $dist->getChild( 'equivset.php' )->url() );
	}

	/**
	 * Provide Invalid Chars
	 */
	public function provideInvalidChar() {
		return [
			[ '30 �� => 4F O' ],
			[ '30 0 => 4F ��' ],
		];
	}

	/**
	 * Test Execute Failure Invalid Character
	 *
	 * Ensure that invalid UTF-8 characters results in a failure of the
	 * generate-equivset command.
	 *
	 * @param string $in Equivset line
	 *
	 * @dataProvider provideInvalidChar
	 */
	public function testExecuteFailInvalidChar( string $in ) {
		$out = [
			0 => 'O',
		];

		[ $data, $dist ] = $this->mockFileSystem( $in );

		$command = new GenerateEquivset( $data->url(), $dist->url() );

		$input = $this->createMock( InputInterface::class );
		$output = $this->createMock( OutputInterface::class );
		$output->method( 'writeln' )
			->withConsecutive(
				[ $this->stringContains( 'Error: invalid entry at line 1:' ) ],
				[ $this->stringContains( 'Finished with errors' ) ]
			);

		$status = $command->execute( $input, $output );

		$this->assertSame( 1, $status );

		$this->assertNotEquals( $out, require $dist->getChild( 'equivset.php' )->url() );
	}

	/**
	 * Test Execute Failure Duplicate Character
	 *
	 * Ensure duplicate chars in the file are detected
	 */
	public function testExecuteFailDuplicateChar() {
		$in = "30 0 => 53 S\n30 0 => 4F O";
		$out = [
			0 => 'O',
		];

		[ $data, $dist ] = $this->mockFileSystem( $in );

		$command = new GenerateEquivset( $data->url(), $dist->url() );

		$input = $this->createMock( InputInterface::class );
		$output = $this->createMock( OutputInterface::class );
		$output->method( 'writeln' )
			->withConsecutive(
				[ $this->stringContains( 'Duplicate character' ) ],
				[ $this->stringContains( 'Finished with errors' ) ]
			);

		$status = $command->execute( $input, $output );

		$this->assertSame( 1, $status );

		$this->assertNotEquals( $out, require $dist->getChild( 'equivset.php' )->url() );
	}

	/**
	 * Test Execute Failure Wrong Order of Character
	 *
	 * Ensure ordered out chars in the file are detected
	 */
	public function testExecuteFailOrderChar() {
		$in = "35 5 => 53 S\n30 0 => 4F O";
		$out = [
			0 => 'O',
		];

		[ $data, $dist ] = $this->mockFileSystem( $in );

		$command = new GenerateEquivset( $data->url(), $dist->url() );

		$input = $this->createMock( InputInterface::class );
		$output = $this->createMock( OutputInterface::class );
		$output->method( 'writeln' )
			->withConsecutive(
				[ $this->stringContains( 'Characters not in order based on hex-value' ) ],
				[ $this->stringContains( 'Finished with errors' ) ]
			);

		$status = $command->execute( $input, $output );

		$this->assertSame( 1, $status );

		$this->assertNotEquals( $out, require $dist->getChild( 'equivset.php' )->url() );
	}

	/**
	 * @return array{vfsStreamDirectory,vfsStreamDirectory}
	 */
	private function mockFileSystem( string $in = null ): array {
		$root = vfsStream::setup();
		$data = vfsStream::newDirectory( 'data' )->at( $root );
		$dist = vfsStream::newDirectory( 'dist' )->at( $root );
		if ( $in ) {
			vfsStream::newFile( 'equivset.in' )->withContent( $in )->at( $data );
		}
		return [ $data, $dist ];
	}

}
