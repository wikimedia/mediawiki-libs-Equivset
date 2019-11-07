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

use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream;
use Wikimedia\Equivset\Exception\EquivsetException;

/**
 * Equiveset
 */
class EquivsetTest extends TestCase {

	/**
	 * @var EquivsetInterface
	 */
	protected $equivset;

	/**
	 * @var array
	 */
	protected $data = [
		'0' => 'O',
	];

	/**
	 * Provide Spoof Data.
	 */
	public function providePositives() {
		return [
			/** Format: username -> spoofing attempt */
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
	 * Some very basic normalization checks
	 *
	 * @param string $userName Normalized Username.
	 * @param string $spooferName Spoofer Username.
	 *
	 * @dataProvider providePositives
	 */
	public function testCheckUnicodeString( $userName, $spooferName ) {
		$equivset = new Equivset();

		$this->assertTrue( $equivset->isEqual( $userName, $spooferName ) );
	}

	/**
	 * Test Load
	 *
	 * Ensure that a mock equivset.ser file can be read without a problem.
	 */
	public function testLoad() {
		$root = vfsStream::setup();
		$file = vfsStream::newFile( 'equivset.ser' )
			->withContent( serialize( $this->data ) )
			->at( $root );
		$equivset = new Equivset( [], $file->url() );

		$this->assertEquals( $this->data, $equivset->all() );
	}

	/**
	 * Test Load Failure
	 *
	 * Ensure that a non-existant file will throw an EquivsetException when data
	 * is loaded.
	 */
	public function testLoadFailNoFile() {
		$root = vfsStream::setup();
		$equivset = new Equivset( [], $root->url() . '/missing' );
		$this->expectException( EquivsetException::class );
		$this->expectExceptionMessage( 'Serialized equivset is missing' );
		$equivset->all();
	}

	/**
	 * Test Load Failure
	 *
	 * Ensure that an unreadable file will throw an EquivsetException when data is
	 * loaded.
	 */
	public function testLoadFailUnreadableFile() {
		$root = vfsStream::setup();
		$file = vfsStream::newFile( 'equivset.ser', 0000 )
			->withContent( serialize( $this->data ) )
			->at( $root );
		$equivset = new Equivset( [], $file->url() );
		$this->expectException( EquivsetException::class );
		$this->expectExceptionMessage( 'Serialized equivset is unreadable' );
		$equivset->all();
	}

	/**
	 * Test Load Failure
	 *
	 * Ensure that a file that cannot be unserialized will throw an
	 * EquivsetException when data is loaded.
	 */
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
	 * Gets the Equivset.
	 */
	protected function getEquivset() {
		if ( !$this->equivset ) {
			$this->equivset = new Equivset( $this->data );
		}

		return $this->equivset;
	}

	/**
	 * Test Get All.
	 */
	public function testAll() {
		$data = $this->getEquivset()->all();
		$this->assertEquals( 'O', $data[0] );
	}

	/**
	 * Test Get All.
	 */
	public function testNormalize() {
		$this->assertEquals( 'O', $this->getEquivset()->normalize( '0' ) );
	}

	/**
	 * Test Get All.
	 */
	public function testIsEqual() {
		$this->assertTrue( $this->getEquivset()->isEqual( '0', '0' ) );
	}

	/**
	 * Tests Traversable.
	 */
	public function testTraversable() {
		$this->assertInstanceOf( \Traversable::class, $this->getEquivset() );
	}

	/**
	 * Test Get Iterator.
	 */
	public function testGetIterator() {
		$data = $this->getEquivset()->getIterator();
		$this->assertEquals( 'O', $data[0] );
		$this->assertInstanceOf( \Traversable::class, $data );
	}

	/**
	 * Test Has.
	 */
	public function testHas() {
		$this->assertTrue( $this->getEquivset()->has( '0' ) );
	}

	/**
	 * Test Has Not.
	 */
	public function testHasNot() {
		$this->assertFalse( $this->getEquivset()->has( 'fail' ) );
	}

	/**
	 * Test Get.
	 */
	public function testGet() {
		$this->assertEquals( 'O', $this->getEquivset()->get( '0' ) );
	}

	/**
	 * Test Get Fail.
	 */
	public function testGetFail() {
		$this->expectException( \LogicException::class );
		$this->getEquivset()->get( 'fail' );
	}

}
