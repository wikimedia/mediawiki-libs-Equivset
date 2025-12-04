<?php
declare( strict_types = 1 );

/**
 * @license GPL-2.0-or-later
 */

namespace Wikimedia\Equivset;

use LogicException;

/**
 * Equivset
 */
interface EquivsetInterface {

	/**
	 * Gets the equivset.
	 *
	 * @return array An associative array of equivalent characters.
	 */
	public function all(): array;

	/**
	 * Normalize a string.
	 *
	 * @param string $value The string to normalize against the equivset.
	 *
	 * @return string
	 */
	public function normalize( string $value ): string;

	/**
	 * Determine if the two strings are visually equal.
	 *
	 * @param string $str1 The first string.
	 * @param string $str2 The second string.
	 *
	 * @return bool
	 */
	public function isEqual( string $str1, string $str2 ): bool;

	/**
	 * Determine if an equivalent character exists.
	 *
	 * @param string $key The character that was used.
	 *
	 * @return bool If the character has an equivalent.
	 */
	public function has( string $key ): bool;

	/**
	 * Get an equivalent character.
	 *
	 * @param string $key The character that was used.
	 *
	 * @return string The equivalent character.
	 *
	 * @throws LogicException If character does not exist.
	 */
	public function get( string $key ): string;
}
