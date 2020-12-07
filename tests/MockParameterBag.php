<?php


namespace App\Tests;


use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class MockParameterBag implements ParameterBagInterface
{

	/**
	 * @inheritDoc
	 */
	public function clear() {
		// TODO: Implement clear() method.
	}

	/**
	 * @inheritDoc
	 */
	public function add(array $parameters) {
		// TODO: Implement add() method.
	}

	/**
	 * @inheritDoc
	 */
	public function all() {
		// TODO: Implement all() method.
	}

	/**
	 * @inheritDoc
	 */
	public function get(string $name) {
		if ($name == 'file_directory') {
			return '/tmp/';
		}
	}

	/**
	 * @inheritDoc
	 */
	public function remove(string $name) {
		// TODO: Implement remove() method.
	}

	/**
	 * @inheritDoc
	 */
	public function set(string $name, $value) {
		// TODO: Implement set() method.
	}

	/**
	 * @inheritDoc
	 */
	public function has(string $name) {
		// TODO: Implement has() method.
	}

	/**
	 * @inheritDoc
	 */
	public function resolve() {
		// TODO: Implement resolve() method.
	}

	/**
	 * @inheritDoc
	 */
	public function resolveValue($value) {
		// TODO: Implement resolveValue() method.
	}

	/**
	 * @inheritDoc
	 */
	public function escapeValue($value) {
		// TODO: Implement escapeValue() method.
	}

	/**
	 * @inheritDoc
	 */
	public function unescapeValue($value) {
		// TODO: Implement unescapeValue() method.
	}
}