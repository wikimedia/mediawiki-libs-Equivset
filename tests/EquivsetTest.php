<?php
declare( strict_types = 1 );

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

namespace Wikimedia\Equivset;

use Error;
use LogicException;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Traversable;

/**
 * @covers \Wikimedia\Equivset\Equivset
 */
class EquivsetTest extends TestCase {

	protected ?EquivsetInterface $equivset = null;

	private const DATA = [
		'0' => 'O',
	];

	public static function providePositives(): iterable {
		return [
			// Format: username -> spoofing attempt
			[ 'Laura Fiorucci', 'Låura Fiorucci' ],
			[ 'Lucien leGrey', 'Lucien le6rey' ],
			[ 'Poco a poco', 'Poco a ƿoco' ],
			[ 'Sabbut', 'ЅаЬЬцт' ],
			[ 'BetoCG', 'ВетоС6' ],
			[ 'Vvanda', 'vv4ndá' ],
			[ 'Rnario', 'rnAr10' ],
			[ 'Recursive Θ Tester', 'Recursive O Tester' ],
			[ 'Recursive Θ Tester', 'Recursive 0 Tester' ],
			[ 'CEASAR', 'ceasar' ],
			[ 'ceasar', 'CEASAR' ],
			[ 'j1mmy w4l35', 'jimmy wales' ]
		];
	}

	/**
	 * @dataProvider providePositives
	 */
	public function testCheckUnicodeString( string $userName, string $spooferName ): void {
		$equivset = new Equivset();

		$this->assertTrue( $equivset->isEqual( $userName, $spooferName ) );
	}

	public function testLoadPhp(): void {
		$root = vfsStream::setup();
		$file = vfsStream::newFile( 'equivset.php' )
			->withContent( '<?php return ' . var_export( self::DATA, true ) . ';' )
			->at( $root );
		$equivset = new Equivset( [], $file->url() );

		$this->assertEquals( self::DATA, $equivset->all() );
	}

	public function testLoadFailNoPhpFile(): void {
		$root = vfsStream::setup();
		$equivset = new Equivset( [], $root->url() . '/missing.php' );
		try {
			set_error_handler( static function () {
				// Suppress the warning text
			}, E_ERROR | E_WARNING );

			$equivset->all();

			$this->fail( "No PHP error was emitted." );
		} catch ( Error $e ) {
			$this->addToAssertionCount( 1 );
		} finally {
			restore_error_handler();
		}
	}

	protected function getEquivset(): Equivset {
		if ( !$this->equivset ) {
			$this->equivset = new Equivset( self::DATA );
		}

		return $this->equivset;
	}

	public function testAll(): void {
		$data = $this->getEquivset()->all();
		$this->assertEquals( 'O', $data[0] );
	}

	public function testNormalize(): void {
		$this->assertEquals( 'O', $this->getEquivset()->normalize( '0' ) );
	}

	public function testIsEqual(): void {
		$this->assertTrue( $this->getEquivset()->isEqual( '0', '0' ) );
	}

	public function testTraversable(): void {
		$this->assertInstanceOf( Traversable::class, $this->getEquivset() );
	}

	public function testGetIterator(): void {
		$data = $this->getEquivset()->getIterator();
		$this->assertEquals( 'O', $data[0] );
		$this->assertInstanceOf( Traversable::class, $data );
	}

	public function testHas(): void {
		$this->assertTrue( $this->getEquivset()->has( '0' ) );
	}

	public function testHasNot(): void {
		$this->assertFalse( $this->getEquivset()->has( 'fail' ) );
	}

	public function testGet(): void {
		$this->assertEquals( 'O', $this->getEquivset()->get( '0' ) );
	}

	public function testGetFail(): void {
		$this->expectException( LogicException::class );
		$this->getEquivset()->get( 'fail' );
	}

}
