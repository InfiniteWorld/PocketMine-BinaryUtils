<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
*/

declare(strict_types=1);

/**
 * Methods for working with binary strings
 */
namespace pocketmine\utils;

use InvalidArgumentException;
use function chr;
use function define;
use function defined;
use function ord;
use function pack;
use function preg_replace;
use function round;
use function sprintf;
use function substr;
use function unpack;
use const PHP_INT_MAX;

if(!defined("ENDIANNESS")){
	define("ENDIANNESS", (pack("s", 1) === "\0\1" ? Binary::BIG_ENDIAN : Binary::LITTLE_ENDIAN));
}

class Binary{
	public const BIG_ENDIAN = 0x00;
	public const LITTLE_ENDIAN = 0x01;

	public static function signByte(int $value) : int{
		return PHP_INT_SIZE === 8 ? ($value << 56 >> 56) : ($value << 24 >> 24);
	}

	public static function unsignByte(int $value) : int{
		return $value & 0xff;
	}

	public static function signShort(int $value) : int{
		return PHP_INT_SIZE === 8 ? ($value << 48 >> 48) : ($value << 16 >> 16);
	}

	public static function unsignShort(int $value) : int{
		return $value & 0xffff;
	}

	public static function signInt(int $value) : int{
		return PHP_INT_SIZE === 8 ? ($value << 32 >> 32) : $value;
	}

	public static function unsignInt(int $value) : int{
		return $value & 0xffffffff;
	}


	public static function flipShortEndianness(int $value) : int{
		return self::readLShort(self::writeShort($value));
	}

	public static function flipIntEndianness(int $value) : int{
		return self::readLInt(self::writeInt($value));
	}

	public static function flipLongEndianness(int $value) : int{
		return self::readLLong(self::writeLong($value));
	}

	/**
	 * Reads a byte boolean
	 *
	 * @param string $b
	 *
	 * @return bool
	 */
	public static function readBool(string $b) : bool{
		return $b !== "\x00";
	}

	/**
	 * Writes a byte boolean
	 *
	 * @param bool $b
	 *
	 * @return string
	 */
	public static function writeBool(bool $b) : string{
		return $b ? "\x01" : "\x00";
	}

	/**
	 * Reads an unsigned byte (0 - 255)
	 *
	 * @param string $c
	 *
	 * @return int
	 */
	public static function readByte(string $c) : int{
		return ord($c{0});
	}

	/**
	 * Reads a signed byte (-128 - 127)
	 *
	 * @param string $c
	 *
	 * @return int
	 */
	public static function readSignedByte(string $c) : int{
		return self::signByte(ord($c{0}));
	}

	/**
	 * Writes an unsigned/signed byte
	 *
	 * @param int $c
	 *
	 * @return string
	 */
	public static function writeByte(int $c) : string{
		return chr($c);
	}

	/**
	 * Reads a 16-bit unsigned big-endian number
	 *
	 * @param string $str
	 *
	 * @return int
	 */
	public static function readShort(string $str) : int{
		return unpack("n", $str)[1];
	}

	/**
	 * Reads a 16-bit signed big-endian number
	 *
	 * @param $str
	 *
	 * @return int
	 */
	public static function readSignedShort(string $str) : int{
		return self::signShort(unpack("n", $str)[1]);
	}

	/**
	 * Writes a 16-bit signed/unsigned big-endian number
	 *
	 * @param int $value
	 *
	 * @return string
	 */
	public static function writeShort(int $value) : string{
		return pack("n", $value);
	}

	/**
	 * Reads a 16-bit unsigned little-endian number
	 *
	 * @param string $str
	 *
	 * @return int
	 */
	public static function readLShort(string $str) : int{
		return unpack("v", $str)[1];
	}

	/**
	 * Reads a 16-bit signed little-endian number
	 *
	 * @param string $str
	 *
	 * @return int
	 */
	public static function readSignedLShort(string $str) : int{
		return self::signShort(unpack("v", $str)[1]);
	}

	/**
	 * Writes a 16-bit signed/unsigned little-endian number
	 *
	 * @param int $value
	 *
	 * @return string
	 */
	public static function writeLShort(int $value) : string{
		return pack("v", $value);
	}

	/**
	 * Reads a 3-byte big-endian number
	 *
	 * @param string $str
	 *
	 * @return int
	 */
	public static function readTriad(string $str) : int{
		return unpack("N", "\x00" . $str)[1];
	}

	/**
	 * Writes a 3-byte big-endian number
	 *
	 * @param int $value
	 *
	 * @return string
	 */
	public static function writeTriad(int $value) : string{
		return substr(pack("N", $value), 1);
	}

	/**
	 * Reads a 3-byte little-endian number
	 *
	 * @param string $str
	 *
	 * @return int
	 */
	public static function readLTriad(string $str) : int{
		return unpack("V", $str . "\x00")[1];
	}

	/**
	 * Writes a 3-byte little-endian number
	 *
	 * @param int $value
	 *
	 * @return string
	 */
	public static function writeLTriad(int $value) : string{
		return substr(pack("V", $value), 0, -1);
	}

	/**
	 * Reads a 4-byte signed integer
	 *
	 * @param string $str
	 *
	 * @return int
	 */
	public static function readInt(string $str) : int{
		return self::signInt(unpack("N", $str)[1]);
	}

	/**
	 * Writes a 4-byte integer
	 *
	 * @param int $value
	 *
	 * @return string
	 */
	public static function writeInt(int $value) : string{
		return pack("N", $value);
	}

	/**
	 * Reads a 4-byte signed little-endian integer
	 *
	 * @param string $str
	 *
	 * @return int
	 */
	public static function readLInt(string $str) : int{
		return self::signInt(unpack("V", $str)[1]);
	}

	/**
	 * Writes a 4-byte signed little-endian integer
	 *
	 * @param int $value
	 *
	 * @return string
	 */
	public static function writeLInt(int $value) : string{
		return pack("V", $value);
	}

	/**
	 * Reads a 4-byte floating-point number
	 *
	 * @param string $str
	 *
	 * @return float
	 */
	public static function readFloat(string $str) : float{
		return unpack("G", $str)[1];
	}

	/**
	 * Reads a 4-byte floating-point number, rounded to the specified number of decimal places.
	 *
	 * @param string $str
	 * @param int    $accuracy
	 *
	 * @return float
	 */
	public static function readRoundedFloat(string $str, int $accuracy) : float{
		return round(self::readFloat($str), $accuracy);
	}

	/**
	 * Writes a 4-byte floating-point number.
	 *
	 * @param float $value
	 *
	 * @return string
	 */
	public static function writeFloat(float $value) : string{
		return pack("G", $value);
	}

	/**
	 * Reads a 4-byte little-endian floating-point number.
	 *
	 * @param string $str
	 *
	 * @return float
	 */
	public static function readLFloat(string $str) : float{
		return unpack("g", $str)[1];
	}

	/**
	 * Reads a 4-byte little-endian floating-point number rounded to the specified number of decimal places.
	 *
	 * @param string $str
	 * @param int    $accuracy
	 *
	 * @return float
	 */
	public static function readRoundedLFloat(string $str, int $accuracy) : float{
		return round(self::readLFloat($str), $accuracy);
	}

	/**
	 * Writes a 4-byte little-endian floating-point number.
	 *
	 * @param float $value
	 *
	 * @return string
	 */
	public static function writeLFloat(float $value) : string{
		return pack("g", $value);
	}

	/**
	 * Returns a printable floating-point number.
	 *
	 * @param float $value
	 *
	 * @return string
	 */
	public static function printFloat(float $value) : string{
		return preg_replace("/(\\.\\d+?)0+$/", "$1", sprintf("%F", $value));
	}

	/**
	 * Reads an 8-byte floating-point number.
	 *
	 * @param string $str
	 *
	 * @return float
	 */
	public static function readDouble(string $str) : float{
		return unpack("E", $str)[1];
	}

	/**
	 * Writes an 8-byte floating-point number.
	 *
	 * @param float $value
	 *
	 * @return string
	 */
	public static function writeDouble(float $value) : string{
		return pack("E", $value);
	}

	/**
	 * Reads an 8-byte little-endian floating-point number.
	 *
	 * @param string $str
	 *
	 * @return float
	 */
	public static function readLDouble(string $str) : float{
		return unpack("e", $str)[1];
	}

	/**
	 * Writes an 8-byte floating-point little-endian number.
	 *
	 * @param float $value
	 *
	 * @return string
	 */
	public static function writeLDouble(float $value) : string{
		return pack("e", $value);
	}

	/**
	 * Reads an 8-byte integer.
	 * Note that this method will return a string on 32-bit PHP.
	 *
	 * @param string $str
	 *
	 * @return int|string
	 */
	public static function readLong(string $str){
		if(PHP_INT_SIZE === 8){
			return unpack("J", $str)[1];
		}else{
			$value = "0";
			for($i = 0; $i < 8; $i += 2){
				$value = bcmul($value, "65536", 0);
				$value = bcadd($value, (string) self::readShort(substr($str, $i, 2)), 0);
			}

			if(bccomp($value, "9223372036854775807") == 1){
				$value = bcadd($value, "-18446744073709551616");
			}

			return $value;
		}
	}

	/**
	 * Writes an 8-byte integer.
	 *
	 * @param int|string $value
	 *
	 * @return string
	 */
	public static function writeLong($value) : string{
		if(PHP_INT_SIZE === 8){
			return pack("J", $value);
		}else{
			$x = "";
			$value = (string) $value;

			if(bccomp($value, "0") == -1){
				$value = bcadd($value, "18446744073709551616");
			}

			$x .= self::writeShort((int) bcmod(bcdiv($value, "281474976710656"), "65536"));
			$x .= self::writeShort((int) bcmod(bcdiv($value, "4294967296"), "65536"));
			$x .= self::writeShort((int) bcmod(bcdiv($value, "65536"), "65536"));
			$x .= self::writeShort((int) bcmod($value, "65536"));

			return $x;
		}
	}

	/**
	 * Reads an 8-byte little-endian integer.
	 *
	 * @param string $str
	 *
	 * @return int|string
	 */
	public static function readLLong(string $str){
		return PHP_INT_SIZE === 8 ? unpack("P", $str)[1] : self::readLong(strrev($str));
	}

	/**
	 * Writes an 8-byte little-endian integer.
	 *
	 * @param int|string $value
	 *
	 * @return string
	 */
	public static function writeLLong($value) : string{
		return PHP_INT_SIZE === 8 ? pack("P", $value) : strrev(self::writeLong($value));
	}


	/**
	 * Reads a 32-bit zigzag-encoded variable-length integer.
	 *
	 * @param string $buffer
	 * @param int    &$offset
	 *
	 * @return int
	 */
	public static function readVarInt(string $buffer, int &$offset) : int{
		$shift = PHP_INT_SIZE === 8 ? 63 : 31;
		$raw = self::readUnsignedVarInt($buffer, $offset);
		$temp = ((($raw << $shift) >> $shift) ^ $raw) >> 1;
		return $temp ^ ($raw & (1 << $shift));
	}

	/**
	 * Reads a 32-bit variable-length unsigned integer.
	 *
	 * @param string $buffer
	 * @param int    &$offset
	 *
	 * @return int
	 *
	 * @throws BinaryDataException if the var-int did not end after 5 bytes or there were not enough bytes
	 */
	public static function readUnsignedVarInt(string $buffer, int &$offset) : int{
		$value = 0;
		for($i = 0; $i <= 28; $i += 7){
			if(!isset($buffer{$offset})){
				throw new BinaryDataException("No bytes left in buffer");
			}
			$b = ord($buffer{$offset++});
			$value |= (($b & 0x7f) << $i);

			if(($b & 0x80) === 0){
				return $value;
			}
		}

		throw new BinaryDataException("VarInt did not terminate after 5 bytes!");
	}

	/**
	 * Writes a 32-bit integer as a zigzag-encoded variable-length integer.
	 *
	 * @param int $v
	 *
	 * @return string
	 */
	public static function writeVarInt(int $v) : string{
		if(PHP_INT_SIZE === 8){
			$v = ($v << 32 >> 32);
		}
		return self::writeUnsignedVarInt(($v << 1) ^ ($v >> 31));
	}

	/**
	 * Writes a 32-bit unsigned integer as a variable-length integer.
	 *
	 * @param int $value
	 *
	 * @return string up to 5 bytes
	 */
	public static function writeUnsignedVarInt(int $value) : string{
		$buf = "";
		$value &= 0xffffffff;
		for($i = 0; $i < 5; ++$i){
			if(($value >> 7) !== 0){
				$buf .= chr($value | 0x80);
			}else{
				$buf .= chr($value & 0x7f);
				return $buf;
			}

			$value = (($value >> 7) & (PHP_INT_MAX >> 6)); //PHP really needs a logical right-shift operator
		}

		throw new InvalidArgumentException("Value too large to be encoded as a VarInt");
	}


	/**
	 * Reads a 64-bit zigzag-encoded variable-length integer from the supplied stream.
	 *
	 * @param string $buffer
	 * @param int    &$offset
	 *
	 * @return int|string
	 */
	public static function readVarLong(string $buffer, int &$offset){
		if(PHP_INT_SIZE === 8){
			return self::readVarLong_64($buffer, $offset);
		}else{
			return self::readVarLong_32($buffer, $offset);
		}
	}

	/**
	 * Legacy BC Math zigzag VarLong reader. Will work on 32-bit or 64-bit, but will be slower than the regular 64-bit method.
	 *
	 * @param string $buffer
	 * @param int    &$offset
	 *
	 * @return string
	 */
	public static function readVarLong_32(string $buffer, int &$offset) : string{
		/** @var string $raw */
		$raw = self::readUnsignedVarLong_32($buffer, $offset);
		$result = bcdiv($raw, "2");
		if(bcmod($raw, "2") === "1"){
			$result = bcsub(bcmul($result, "-1"), "1");
		}

		return $result;
	}

	/**
	 * 64-bit zizgag VarLong reader.
	 *
	 * @param string $buffer
	 * @param int    &$offset
	 *
	 * @return int
	 */
	public static function readVarLong_64(string $buffer, int &$offset) : int{
		$raw = self::readUnsignedVarLong_64($buffer, $offset);
		$temp = ((($raw << 63) >> 63) ^ $raw) >> 1;
		return $temp ^ ($raw & (1 << 63));
	}

	/**
	 * Reads an unsigned VarLong from the supplied stream.
	 *
	 * @param string $buffer
	 * @param int    &$offset
	 *
	 * @return int|string
	 */
	public static function readUnsignedVarLong(string $buffer, int &$offset){
		if(PHP_INT_SIZE === 8){
			return self::readUnsignedVarLong_64($buffer, $offset);
		}else{
			return self::readUnsignedVarLong_32($buffer, $offset);
		}
	}

	/**
	 * Legacy BC Math unsigned VarLong reader.
	 *
	 * @param string $buffer
	 * @param int    &$offset
	 *
	 * @return string
	 */
	public static function readUnsignedVarLong_32(string $buffer, int &$offset) : string{
		$value = "0";
		for($i = 0; $i <= 63; $i += 7){
			$b = ord($buffer{$offset++});
			$value = bcadd($value, bcmul((string) ($b & 0x7f), bcpow("2", "$i")));

			if(($b & 0x80) === 0){
				return $value;
			}elseif(!isset($buffer{$offset})){
				throw new \UnexpectedValueException("Expected more bytes, none left to read");
			}
		}

		throw new \InvalidArgumentException("VarLong did not terminate after 10 bytes!");
	}

	/**
	 * 64-bit unsigned VarLong reader.
	 *
	 * @param string $buffer
	 * @param int    &$offset
	 *
	 * @return int
	 *
	 * @throws BinaryDataException if the var-int did not end after 10 bytes or there were not enough bytes
	 */
	public static function readUnsignedVarLong_64(string $buffer, int &$offset) : int{
		$value = 0;
		for($i = 0; $i <= 63; $i += 7){
			if(!isset($buffer{$offset})){
				throw new BinaryDataException("No bytes left in buffer");
			}
			$b = ord($buffer{$offset++});
			$value |= (($b & 0x7f) << $i);

			if(($b & 0x80) === 0){
				return $value;
			}
		}

		throw new BinaryDataException("VarLong did not terminate after 10 bytes!");
	}

	/**
	 * Writes a 64-bit integer as a variable-length long.
	 *
	 * @param int|string $v
	 * @return string up to 10 bytes
	 */
	public static function writeVarLong(int $v) : string{
		if(PHP_INT_SIZE === 8){
			return self::writeVarLong_64($v);
		}else{
			return self::writeVarLong_32((string) $v);
		}
	}

	/**
	 * Legacy BC Math zigzag VarLong encoder.
	 *
	 * @param string $v
	 * @return string
	 */
	public static function writeVarLong_32(string $v) : string{
		$v = bcmod(bcmul($v, "2"), "18446744073709551616");
		if(bccomp($v, "0") == -1){
			$v = bcsub(bcmul($v, "-1"), "1");
		}

		return self::writeUnsignedVarLong_32($v);
	}

	/**
	 * 64-bit VarLong encoder.
	 *
	 * @param int $v
	 *
	 * @return string
	 */
	public static function writeVarLong_64(int $v) : string{
		return self::writeUnsignedVarLong_64(($v << 1) ^ ($v >> 63));
	}

	/**
	 * Writes a 64-bit unsigned integer as a variable-length long.
	 *
	 * @param int|string $v
	 * @return string up to 10 bytes
	 */
	public static function writeUnsignedVarLong(int $v) : string{
		if(PHP_INT_SIZE === 8){
			return self::writeUnsignedVarLong_64($v);
		}else{
			return self::writeUnsignedVarLong_32((string) $v);
		}
}

	/**
	 * Legacy BC Math unsigned VarLong encoder.
	 *
	 * @param string $v
	 * @return string
	 */
	public static function writeUnsignedVarLong_32(string $v) : string{
		$buf = "";

		if(bccomp($v, "0") == -1){
			$v = bcadd($v, "18446744073709551616");
		}

		for($i = 0; $i < 10; ++$i){
			$byte = (int) bcmod($v, "128");
			$v = bcdiv($v, "128");
			if($v !== "0"){
				$buf .= chr($byte | 0x80);
			}else{
				$buf .= chr($byte);
				return $buf;
			}
		}

		throw new \InvalidArgumentException("Value too large to be encoded as a VarLong");
	}

	/**
	 * 64-bit unsigned VarLong encoder.
	 * @param int $v
	 *
	 * @return string
	 */
	public static function writeUnsignedVarLong_64(int $v) : string{
		$buf = "";
		for($i = 0; $i < 10; ++$i){
			if(($v >> 7) !== 0){
				$buf .= chr($v | 0x80); //Let chr() take the last byte of this, it's faster than adding another & 0x7f.
			}else{
				$buf .= chr($v & 0x7f);
				return $buf;
			}

			$v = (($v >> 7) & (PHP_INT_MAX >> 6)); //PHP really needs a logical right-shift operator
		}

		throw new InvalidArgumentException("Value too large to be encoded as a VarLong");
	}
}
