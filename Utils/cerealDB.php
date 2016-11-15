<?php
/* ########    USAGE    ##############
include 'cerealDb.php';
$x->collectionBaseDir('/tmp');
$x->useCollection('testing.cdb');
$x->insert("test","1234567890");
echo $x->read("test")."\n";
$x->update("test","0987654321");
echo $x->read("test")."\n";
$x->delete("test");
echo $x->read("test")."\n";
*/

/**
* cerealDb, Serialized Array Storage
* @author Mike Curry
* @version 1.0
*
* Copyright (C) 2010 Mike Curry
*
* This program is free software; you can redistribute it and/or modify it
* under the terms of the GNU General Public License as published by the
* Free Software Foundation; either version 2 of the License, or any later
* version.
*
* This program is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General
* Public License for more details.
*
* You should have received a copy of the GNU General Public License along
* with this program; if not, write to the Free Software Foundation, Inc.,
* 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
*
*/

// CerealDb - Serialized array storage
Class cerealDb {

	// location of storage
	private $_collectionBaseDir;
	private $_collectionName;
	private $_lastWriteDate;

	public $data = array();

	public function __construct() {
	}

	public function collectionBaseDir($directory) {
		if (!is_dir($directory)) {
			return false;
		}

		if (substr($directory, -1, 1) != DIRECTORY_SEPARATOR) {
			$directory .= DIRECTORY_SEPARATOR;
		}

		$this->_collectionBaseDir = $directory;

		return true;
	}

	public function useCollection($collection) {

		$this->_collectionName = trim($collection);

		$file = $this->_collectionBaseDir.$collection;

		// open for write, to create if missing
		if ($fhandle = fopen($file, 'a+')) {
			if (flock($fhandle, LOCK_EX)) {
				if (filesize($file) > 0) {
					$data = fread($fhandle, filesize($file));
					if (strlen($data)) {
						$this->data = unserialize($data);
					} else {
						$this->data = array();
					}
				}
			flock($fhandle, LOCK_UN); // release the lock
			}

			fclose($fhandle);

			$this->_lastWriteDate = filemtime($file);
		}

		return true;
	}

	public function insert($key, $data) {

		if (strlen($this->_collectionName) == 0 ||
			strlen($this->_collectionBaseDir) == 0) {
			return false;
		}

		$result = false;
		$file = $this->_collectionBaseDir.$this->_collectionName;

		if ($fhandle = fopen($file, 'r+')) {
			if (flock($fhandle, LOCK_EX)) {

				// refresh file?
				if (filemtime($file) != $this->_lastWriteDate) {
					if (filesize($file) > 0) {
						$this->data = unserialize(fread($fhandle, filesize($file)));
					}
				}

				// only update if it doesn't exist
				if (!isset($this->data[$key])) {
					$this->data[$key] = $data;

					// trunicate file, and update
					ftruncate($fhandle, 0);
					fwrite($fhandle, serialize($this->data));
					$result = true;
				}

				flock($fhandle, LOCK_UN); // release the lock
			}
			fclose($fhandle);

			// update file time
			$this->_lastWriteDate = filemtime($file);
		}

		return $result;
	}

	public function update($key, $data) {

		if (strlen($this->_collectionName) == 0 ||
			strlen($this->_collectionBaseDir) == 0) {
			return false;
		}

		$result = false;
		$file = $this->_collectionBaseDir.$this->_collectionName;

		if ($fhandle = fopen($file, 'r+')) {
			if (flock($fhandle, LOCK_EX)) {

				// refresh file?
				if (filemtime($file) != $this->_lastWriteDate) {
					if (filesize($file) > 0) {
						$this->data = unserialize(fread($fhandle, filesize($file)));
					}
				}

					// only update if exists
				if (isset($this->data[$key])) {
					$this->data[$key] = $data;

					// trunicate file, and update
					ftruncate($fhandle, 0);
					fwrite($fhandle, serialize($this->data));
					$result = true;
				}

				flock($fhandle, LOCK_UN); // release the lock
			}
			fclose($fhandle);

			// update file time
			$this->_lastWriteDate = filemtime($file);
		}
		return $result;

	}

	public function delete($key) {

		if (strlen($this->_collectionName) == 0 ||
			strlen($this->_collectionBaseDir) == 0) {
			return false;
		}

		$result = false;
		$file = $this->_collectionBaseDir.$this->_collectionName;

		if ($fhandle = fopen($file, 'r+')) {
			if (flock($fhandle, LOCK_EX)) {

				// refresh file?
				if (filemtime($file) != $this->_lastWriteDate) {
					if (filesize($file) > 0) {
						$this->data = unserialize(fread($fhandle, filesize($file)));
					}
				}

				// only delete if exists
				if (isset($this->data[$key])) {
					unset($this->data[$key]);

					// trunicate file, and update
					ftruncate($fhandle, 0);
					fwrite($fhandle, serialize($this->data));
					$result = true;
				}

			flock($fhandle, LOCK_UN); // release the lock
		}
		fclose($fhandle);

		// update file time
		$this->_lastWriteDate = filemtime($file);
		}
		return $result;
	}

	public function read($key) {

		if (strlen($this->_collectionName) == 0 ||
			strlen($this->_collectionBaseDir) == 0) {
			return false;
		}

		$result = false;
		$file = $this->_collectionBaseDir.$this->_collectionName;

		if ($fhandle = fopen($file, 'r+')) {
			if (flock($fhandle, LOCK_EX)) {

				// refresh file?
				if (filemtime($file) != $this->_lastWriteDate) {
					if (filesize($file) > 0) {
						$this->data = unserialize(fread($fhandle, filesize($file)));
					}
				}

				// only return data if exists
				if (isset($this->data[$key])) {
					$result = $this->data[$key];
				}

				flock($fhandle, LOCK_UN); // release the lock
			}
			fclose($fhandle);

			// update file time
			$this->_lastWriteDate = filemtime($file);
		}
		return $result;
	}
}


?>