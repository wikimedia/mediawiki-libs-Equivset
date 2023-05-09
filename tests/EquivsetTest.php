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

namespace Wikimedia\Equivset;

use LogicException;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Traversable;
use Wikimedia\Equivset\Exception\EquivsetException;

/**
 * @covers \Wikimedia\Equivset\Equivset
 */
class EquivsetTest extends TestCase {

	/** @var EquivsetInterface|null */
	protected ?EquivsetInterface $equivset = null;

	/** @var array */
	protected array $data = [
		'0' => 'O',
	];

	public function providePositives() {
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
	 * @param string $userName Normalized Username.
	 * @param string $spooferName Spoofer Username.
	 * @dataProvider providePositives
	 */
	public function testCheckUnicodeString( string $userName, string $spooferName ) {
		$equivset = new Equivset();

		$this->assertTrue( $equivset->isEqual( $userName, $spooferName ) );
	}

	public function testLoadSer() {
		$root = vfsStream::setup();
		$file = vfsStream::newFile( 'equivset.ser' )
			->withContent( serialize( $this->data ) )
			->at( $root );
		$equivset = new Equivset( [], $file->url() );

		$this->assertEquals( $this->data, $equivset->all() );
	}

	public function testLoadPhp() {
		$root = vfsStream::setup();
		$file = vfsStream::newFile( 'equivset.php' )
			->withContent( '<?php return ' . var_export( $this->data, true ) . ';' )
			->at( $root );
		$equivset = new Equivset( [], $file->url() );

		$this->assertEquals( $this->data, $equivset->all() );
	}

	public function testLoadFailNoSerFile() {
		$root = vfsStream::setup();
		$equivset = new Equivset( [], $root->url() . '/missing.ser' );
		$this->expectException( EquivsetException::class );
		$this->expectExceptionMessage( 'Serialized equivset file is unreadable' );
		$equivset->all();
	}

	public function testLoadFailNoPhpFile() {
		$root = vfsStream::setup();
		$equivset = new Equivset( [], $root->url() . '/missing.php' );
		$this->expectError();
		$equivset->all();
	}

	public function testLoadFailUnreadableFile() {
		$root = vfsStream::setup();
		$file = vfsStream::newFile( 'equivset.ser', 0000 )
			->withContent( serialize( $this->data ) )
			->at( $root );
		$equivset = new Equivset( [], $file->url() );
		$this->expectException( EquivsetException::class );
		$this->expectExceptionMessage( 'Serialized equivset file is unreadable' );
		$equivset->all();
	}

	public function testLoadFailUnseriableFile() {
		$root = vfsStream::setup();
		$file = vfsStream::newFile( 'equivset.ser' )
			->withContent( '' )
			->at( $root );
		$equivset = new Equivset( [], $file->url() );
		$this->expectException( EquivsetException::class );
		$this->expectExceptionMessage( 'Unserializing serialized equivset failed' );
		$equivset->all();
	}

	/**
	 * @return Equivset
	 */
	protected function getEquivset() {
		if ( !$this->equivset ) {
			$this->equivset = new Equivset( $this->data );
		}

		return $this->equivset;
	}

	public function testAll() {
		$data = $this->getEquivset()->all();
		$this->assertEquals( 'O', $data[0] );
	}

	public function testNormalize() {
		$this->assertEquals( 'O', $this->getEquivset()->normalize( '0' ) );
	}

	public function testIsEqual() {
		$this->assertTrue( $this->getEquivset()->isEqual( '0', '0' ) );
	}

	public function testTraversable() {
		$this->assertInstanceOf( Traversable::class, $this->getEquivset() );
	}

	public function testGetIterator() {
		$data = $this->getEquivset()->getIterator();
		$this->assertEquals( 'O', $data[0] );
		$this->assertInstanceOf( Traversable::class, $data );
	}

	public function testHas() {
		$this->assertTrue( $this->getEquivset()->has( '0' ) );
	}

	public function testHasNot() {
		$this->assertFalse( $this->getEquivset()->has( 'fail' ) );
	}

	public function testGet() {
		$this->assertEquals( 'O', $this->getEquivset()->get( '0' ) );
	}

	public function testGetFail() {
		$this->expectException( LogicException::class );
		$this->getEquivset()->get( 'fail' );
	}

}
