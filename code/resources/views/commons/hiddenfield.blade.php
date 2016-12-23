<input type="hidden" name="{{ $prefix . $name . $postfix }}" value="{{ $obj ? $obj->$name : '' }}" />
