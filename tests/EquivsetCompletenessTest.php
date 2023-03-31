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
use UtfNormal\Utils;

/**
 * @coversNothing
 */
class EquivsetCompletenessTest extends TestCase {

	/**
	 * For each letter the lower and upper case letter should be part of the set
	 * This test can fail on newer php version due to changes in the underlying Unicode mapping (T292552)
	 */
	public function testIncludeUpperLower() {
		$equivset = new Equivset();
		$data = $equivset->all();

		$missing = '';
		foreach ( $data as $char => $replacement ) {
			$ucChar = mb_strtoupper( $char );
			if ( mb_strlen( $ucChar ) === 1
				&& $char !== $ucChar
				&& $ucChar !== $replacement
				&& !isset( $data[$ucChar] )
				&& !in_array( $ucChar, $data, true )
			) {
				$missing .= 'Upper case character ' . self::printChar( $ucChar ) . ' not in the set ' .
					'(mapping based on lower case character is ' . self::printChar( $replacement ) . ")\n";
			}

			$lcChar = mb_strtolower( $char );
			if ( mb_strlen( $ucChar ) === 1
				&& $char !== $lcChar
				&& $lcChar !== $replacement
				&& !isset( $data[$lcChar] )
				&& $char !== 'İ'
			) {
				$missing .= 'Lower case character ' . self::printChar( $lcChar ) . ' not in the set ' .
					'(mapping based on upper case character is ' . self::printChar( $replacement ) . ")\n";
			}
		}

		$this->assertSame( '', $missing );
	}

	private static function printChar( $char ) {
		return $char . ' (' . strtoupper( dechex( Utils::utf8ToCodepoint( $char ) ) ) . ')';
	}
}
