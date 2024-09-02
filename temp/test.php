<?php
// phpcs:disabled


interface MyInterface {
	const CONSTANT_NAME = 1;

	public function methodName();
}

class MyClass implements MyInterface {
	public function methodName() {

	}
}

interface Readable {
	public function read();
}

interface Document extends Readable {
	public function getContents();
}

class Test implements Document {
	public function getContents()
	{
		//
	}

	public function read() {
		//
	}
}