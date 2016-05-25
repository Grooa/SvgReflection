<?php

namespace Plugin\Clear;

class Job {

	private static $units = array(
		'pt' => 1.25,
		'pc' => 15,
		'mm' => 3.543307,
		'cm' => 35.43307,
		'in' => 90
	);

	private static function toPixels($x) {
		if (preg_match('/^\d+\.?\d*(pc|pc|mm|in|cm)$/', $x, $matches)) {
			return self::$units[$matches[1]] * $x;
		}

		return intval($x);
	}

	/**
	 * TODO: Move elsewhere. Separate plugin?
	 */
	public static function ipCreateReflection_40($data) {

		//TODO: error checking?

		$options = $data['options'];
		$source = $data['source'];
		$destination = $data['destination'];

		// Only handle svg
		if (substr($source, -4, 4) !== '.svg') {
			return null;
		}

		// Read svg
		$doc = new \DOMDocument();
		$doc->load($source);
		$svgTag = $doc->documentElement;

		//TODO: Handle units and missing
		$width = self::toPixels($svgTag->getAttribute('width'));
		$height = self::toPixels($svgTag->getAttribute('height'));
		$viewBox = explode(' ', $svgTag->getAttribute('viewBox'));

		switch ($options['type']) {

		case 'crop':
			//TODO: Verify all these o.O
			$x1 = $options['x1'] / $width;
			$y1 = $options['y1'] / $height;
			$x2 = $options['x2'] / $width;
			$y2 = $options['y2'] / $height;

			$svgTag->setAttribute('width', $options['width']);
			$svgTag->setAttribute('height', $options['height']);

			$svgTag->setAttribute(
				'viewBox',
				implode(
					' ',
					array(
						$viewBox[0] + $x1 * $viewBox[2],
						$viewBox[1] + $y1 * $viewBox[3],
						$viewBox[2] * ($x2 - $x1),
						$viewBox[3] * ($y2 - $y1)
					)
				)
			);

			error_log("
				$width x $height
				${options['width']} x ${options['height']}
				($x1, $y1), ($x2, $y2)
			");

			$doc->save($destination);

			return true;


		case 'fit':
			//TODO: Verify height and width are present
			$newWidth = $options['width'];
			$newHeight = $options['height'];

			// Set new size
			$svgTag->setAttribute('width', $newWidth);
			$svgTag->setAttribute('height', $newHeight);

			if (!$svgTag->hasAttribute('viewBox')) {
				$svgTag->setAttribute('viewBox', "0 0 $width $height");
			}

			// TODO: Account for existing attributes
			$svgTag->setAttribute('preserveAspectRatio', 'xMidyMid meet');

			$doc->save($destination);

			return true;


		default:
			// Let another job handle this
			return null;
		}

	}

}
