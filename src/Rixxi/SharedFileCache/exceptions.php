<?php

namespace Rixxi\SharedFileCache;


interface Exception
{
}


/**
 * The exception that is thrown when a method call is invalid for the object's
 * current state, method has been invoked at an illegal or inappropriate time.
 */
class InvalidStateException extends \RuntimeException implements Exception
{
}


/**
 * The exception that is thrown when an I/O error occurs.
 */
class IOException extends \RuntimeException implements Exception
{
}


/**
 * The exception that is thrown when an argument does not match with the expected value.
 */
class InvalidArgumentException extends \InvalidArgumentException implements Exception
{
}


/**
 * The exception that is thrown when a value (typically returned by function) does not match with the expected value.
 */
class UnexpectedValueException extends \UnexpectedValueException implements Exception
{
}
