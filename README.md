#CryptoLib v0.9

CryptoLib: secure, free & open-source PHP cryptography library for everyone. It makes it easy for those who would usually
implement incredibly weak cryptography to include secure cryptographic functions to secure passwords, data and generate
secure random data.

CryptoLib boasts:
- Secure pseudorandom number/hex/string generator with repeatability checker
- Salted PBKDF2 hashing using alternations between SHA512 and Whirlpool
- Two-way cascading encryption using Rijndael 256, Twofish and Serpent

CryptoLib requires PHP 5.5.0+ (with OpenSSL and MCrypt version 2.4.x or greater). You should always keep your Cryptography plugins
up-to-date.

Check out the documentation at: http://cryptolib.ju.je

##Why I Made It

"We have technology but lack philosophy"; this saying holds true in the world of cryptography when merged with PHP. As a software engineer, working with PHP day-in day-out; it is apparent that unskilled developers still implement cryptographic function unsafely in PHP. Whether it is people hashing passwords without salts, weak random number generation or even encryption systems using weak ciphers. Yes, some may hold the view that PHP Developers are not best equipped for this issue, which is why I built CryptoLib. CryptoLib provides cryptographic functions with a degree of security that is exceptional and unique for any single library to be called with just a single line of simple code. CryptoLib exists to make it easy for people to ensure their cryptographic functions in PHP, for example hashing passwords to store them in a database, hold a reasonable degree of cryptographic safety. This class makes this technology accessible to all, whether developers will lift their heads out of the darkness and take advantage of it is another matter. 

## Version log

- v0.8 Christmas - Initial version, released on Christmas day 2014.
- v0.9 Converging - Documentation updates, released 28th December 2014.

## Warning

No encryption system can make something impossible to crack, it can make this very (very, very, very) difficult to crack;
cryptography is about creating a puzzle which is mathematically harder to solve than create. This applies to everything cryptographic and this library is no exception,
always be careful with encryption when people's lives are on the line.

CryptoLib is version 0.8, this is not nessecarily the finalised code, despite my efforts to ensure it is secure; always be cautious when using it.
Also please note that CryptoLib does not guarantee your server is secure.

## Terms

CryptoLib is an open-source PHP Cryptography library.
Copyright (C) 2014  Junade Ali

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU Affero General Public License as
published by the Free Software Foundation, either version 3 of the
License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.

## Note

You are required to keep attribution notices to the original author intact.
