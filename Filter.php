<?php

namespace Plugin\Clear;


class Filter
{

	public static function ipReflectionExtension_60($ext, $data) {

		// TODO: Validate input
		if (
			substr($data['source'], -4, 4) == 'svg' &&
			in_array($data['options']['type'], array('crop', 'fit'))
		) {
			return 'svg';
		}

		return $ext;
	}

}
