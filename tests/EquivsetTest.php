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

/**
 * Equiveset
 */
class EquivsetTest extends TestCase {

	/**
	 * Test Get All.
	 */
	public function testAll() {
		$equivset = new Equivset();

		$data = $equivset->all();
		$this->assertEquals( 'O', $data[0] );
	}

	/**
	 * Test Get All.
	 */
	public function testNormalize() {
		$equivset = new Equivset();

		$this->assertEquals( 'O', $equivset->normalize( 0 ) );
	}

	/**
	 * Tests Traversable.
	 */
	public function testTraversable() {
		$equivset = new Equivset();

		$this->assertInstanceOf( \Traversable::class, $equivset );
	}

	/**
	 * Test Get Iterator.
	 */
	public function testGetIterator() {
		$equivset = new Equivset();

		$data = $equivset->getIterator();
		$this->assertEquals( 'O', $data[0] );
		$this->assertInstanceOf( \Traversable::class, $data );
	}

	/**
	 * Test Has.
	 */
	public function testHas() {
		$equivset = new Equivset();

		$this->assertTrue( $equivset->has( 0 ) );
	}

	/**
	 * Test Has Not.
	 */
	public function testHasNot() {
		$equivset = new Equivset();

		$this->assertFalse( $equivset->has( 'fail' ) );
	}

	/**
	 * Test Get.
	 */
	public function testGet() {
		$equivset = new Equivset();

		$this->assertEquals( 'O', $equivset->get( 0 ) );
	}

	/**
	 * Test Get Fail.
	 *
	 * @expectedException
	 */
	public function testGetFail() {
		$this->setExpectedException( \LogicException::class );

		$equivset = new Equivset();

		$equivset->get( 'fail' );
	}

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
			[ 'Rnario', 'rnAr10' ]
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

		$this->assertEquals( $equivset->normalize( $userName ), $equivset->normalize( $spooferName ) );
	}

}
