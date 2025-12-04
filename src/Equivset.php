<?php
declare( strict_types = 1 );

/**
 * @license GPL-2.0-or-later
 */

namespace Wikimedia\Equivset;

use ArrayIterator;
use IteratorAggregate;
use LogicException;

/**
 * Default Equivset
 */
class Equivset implements EquivsetInterface, IteratorAggregate {

	/**
	 * @var array<string,string>
	 */
	protected array $data;

	protected readonly string $dataPath;

	/**
	 * @param array<string,string> $data Equivalent Set
	 * @param string $dataPath Path of the equivset array.
	 */
	public function __construct( array $data = [], string $dataPath = '' ) {
		$this->data = $data;
		$this->dataPath = $dataPath ?: __DIR__ . '/../dist/equivset.php';
	}

	/** {@inheritdoc} */
	public function all(): array {
		if ( !$this->data ) {
			$this->data = $this->load();
		}

		return $this->data;
	}

	/** {@inheritdoc} */
	public function normalize( string $value ): string {
		$data = $this->all();

		return strtr( $value, $data );
	}

	/** {@inheritdoc} */
	public function isEqual( string $str1, string $str2 ): bool {
		return $this->normalize( $str1 ) === $this->normalize( $str2 );
	}

	/** {@inheritdoc} */
	public function has( string $key ): bool {
		$data = $this->all();

		return array_key_exists( $key, $data );
	}

	/** {@inheritdoc} */
	public function get( string $key ): string {
		$data = $this->all();

		if ( !array_key_exists( $key, $data ) ) {
			throw new LogicException( 'Equivalent Character Not Found' );
		}

		return $data[$key];
	}

	/** {@inheritdoc} */
	public function getIterator(): ArrayIterator {
		return new ArrayIterator( $this->all() );
	}

	/**
	 * Get the equivset.
	 *
	 * @return array<string,string> An associative array of equivalent characters.
	 */
	protected function load(): array {
		// This will naturally throw if the file does not exist, is not readable,
		// or can't be parsed.
		return require $this->dataPath;
	}
}
