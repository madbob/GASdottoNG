<?php

namespace App;

use Illuminate\Http\Request;

trait AttachableTrait
{
	public function attachments()
	{
		return $this->morphMany('App\Attachment', 'target');
	}

	public function attachByRequest($request)
	{
		$file = $request->file('file');

		if ($file == null || $file->isValid() == false)
			return false;

		$filepath = $this->filesPath();
		if ($filepath == null)
			return false;

		$filename = $file->getClientOriginalName();
		$file->move($filepath, $filename);

		$name = $request->input('filename', '');
		if ($name == '')
			$name = $filename;

		$attachment = new Attachment();
		$attachment->name = $name;
		$attachment->filename = $filename;
		$attachment->save();

		$this->attachments()->save($attachment);
		return $attachment;
	}

	protected function requiredAttachmentPermission()
	{
		return null;
	}

	public function filesPath()
	{
		$path = sprintf('%s/%s', storage_path(), $this->name);
		if (file_exists($path) == false)
			if (mkdir($path) == false)
				return null;

		return $path;
	}

	public function attachmentPermissionGranted()
	{
		if (array_search('App\AllowableTrait', class_uses($this)) !== false) {
			$permission = $this->requiredAttachmentPermission();
			if ($permission == null)
				return true;

			return $this->userCan($permission);
		}
		else {
			return true;
		}
	}
}
