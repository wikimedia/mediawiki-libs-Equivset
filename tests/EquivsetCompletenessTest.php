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

use InvalidArgumentException;
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
			// Skip when the corresponding character is a composite and not an individual codepoint
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
			// Skip when the corresponding character is a composite and not an individual codepoint
			if ( mb_strlen( $lcChar ) === 1
				&& $char !== $lcChar
				&& $lcChar !== $replacement
				&& !isset( $data[$lcChar] )
			) {
				$missing .= 'Lower case character ' . self::printChar( $lcChar ) . ' not in the set ' .
					'(mapping based on upper case character is ' . self::printChar( $replacement ) . ")\n";
			}
		}

		$this->assertSame( '', $missing );
	}

	public function testLowercaseStrictlyEqualsUppercase() {
		$inputMap = [];

		$fp = fopen( __DIR__ . '/../data/equivset.in', 'rb' );
		// phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition.FoundInWhileCondition
		while ( ( $line = fgets( $fp ) ) !== false ) {
			if ( !trim( $line ) || str_starts_with( $line, '#' ) ) {
				continue;
			}

			$this->assertSame( 1, preg_match(
				'/^\w+\h+([^\t ])\h*=>\h*\w*\h*([^\t ])/u', $line, $matches
			), "Failed parsing \"$line\"" );
			$inputMap[$matches[1]] = $matches[2];
		}

		$equivset = new Equivset();
		$errors = '';

		foreach ( $inputMap as $char => $replacement ) {
			$char = (string)$char;

			$ucChar = mb_strtoupper( $char );
			if ( $char !== $ucChar &&
				$ucChar !== $replacement &&
				$equivset->isEqual( $char, $ucChar ) &&
				( !isset( $inputMap[$ucChar] ) || $inputMap[$ucChar] !== $char )
			) {
				$errors .= 'Please map lowercase ' . self::printChar( $char ) . ' and uppercase ' .
					self::printChar( $ucChar ) . " directly to each other\n";
				continue;
			}

			$lcChar = mb_strtolower( $char );
			if ( $char !== $lcChar &&
				$lcChar !== $replacement &&
				mb_strtoupper( $lcChar ) !== $replacement &&
				$equivset->isEqual( $char, $lcChar ) &&
				( !isset( $inputMap[$lcChar] ) || $inputMap[$lcChar] !== $char )
			) {
				$errors .= 'Please map uppercase ' . self::printChar( $char ) . ' and lowercase ' .
					self::printChar( $lcChar ) . " directly to each other\n";
			}
		}

		$this->assertSame( '', $errors );
	}

	private static function printChar( string $char ): string {
		if ( mb_strlen( $char ) !== 1 ) {
			throw new InvalidArgumentException( '"' . $char . '" is not a character' );
		}

		return '"' . strtoupper( dechex( Utils::utf8ToCodepoint( $char ) ) ) . ' ' . $char . '"';
	}

}
