<?php

/**
 * The PDF generation functionality
 *
 * PHP version 5
 *
 * @license		https://github.com/TrustTheVote-Project/horatio-server/blob/master/LICENSE
 * @version		1.0
 * @link		https://github.com/TrustTheVote-Project/horatio-server/
 * @since		1.0
 *
 */

include("includes/mpdf60/mpdf.php");

/*
 * Create a new instance of mPDF, using an 11-point font.
 */
$mpdf = new mPDF('', '', 11); 

/*
 * Enable the use of imported PDFs.
 */
$mpdf->SetImportUse(); 

/*
 * Load the SBE's blank form as our template.
 */
$mpdf->SetSourceFile('includes/application.pdf');
$template = $mpdf->ImportPage(1);
$mpdf->UseTemplate($template);

$form = json_decode(file_get_contents('includes/application-map.json'));

/*
 * Iterate through every section of the form field.
 */
foreach ($form as $section_name => $section)
{

	/*
	 * Iterate through every field in this section, if it's broken into sections.
	 */
	if (!isset($section->coordinates))
	{

		foreach ($section as $field_name => $field)
		{

			/*
			 * All manner of special values trip up mPDF, so make sure 
			 */
			$value = utf8_encode($values->$section_name->$field_name);

			/*
			 * Convert data to the format in which it needs to appear on the form.
			 */
			if ( ($section_name == 'signature') && ($field_name == 'date') )
			{
				$value = date('m d y', strtotime($value));
			}
			elseif ( ($section_name == 'change') && ($field_name == 'date_moved') )
			{
				if ( isset($value) && !empty($value) )
				{
					$value = date('m d y', strtotime($value));
				}
			}
			elseif ( ($section_name == 'election') && ($field_name == 'date') )
			{
				$value = date('m d y', strtotime($value));
			}
			elseif ( ($section_name == 'election') && ($field_name == 'type') )
			{

				if ($value == 'Democratic Primary') $x = $field->coordinates->x + 50;
				elseif ($value == 'Republican Primary') $x = $field->coordinates->x + 89.1;
				else $x = $field->coordinates->x;
				$field->coordinates->x = $x;
				$value = 'x';

			}
			elseif ( ($section_name == 'election') && ($field_name == 'locality') )
			{

				if (substr($value, -7) == ' County')
				{
					$value = substr($value, 0, -7);
					$mpdf->WriteText(116, 65, 'x');
				}
				elseif (substr($value, -5) == ' City')
				{
					$value = substr($value, 0, -5);
					$mpdf->WriteText(132, 65, 'x');
				}

			}
			elseif ( ($section_name == 'more_info') && ($field_name == 'telephone') )
			{
				$value = str_replace('-', '', $value);
				$value = str_replace('(', '', $value);
				$value = str_replace(')', '', $value);
				$value = str_replace(' ', '', $value);
				if (strlen($value) == 10)
				{
					$value = substr($value, 0, 3) . ' ' . substr($value, 3, 3) . ' '
						. substr($value, -4);
				}
			}

			elseif ( ($section_name == 'delivery') && ($field_name == 'to') )
			{

				if ($value == 'mailing address')
				{
					$x = $field->coordinates->x + 58.3;
					$y = $field->coordinates->y + 0.1;
				}
				elseif ($value == 'email')
				{
					$x = $field->coordinates->x;
					$y = $field->coordinates->y + 4.8;
				}
				elseif ($value == 'fax')
				{
					$x = $field->coordinates->x + 58.3;
					$y = $field->coordinates->y + 4.9;
				}
				else
				{
					$x = $field->coordinates->x;
					$y = $field->coordinates->y;
				}
				$field->coordinates->x = $x;
				$field->coordinates->y = $y;
				$value = 'x';
				
			}
			elseif ( ($section_name == 'delivery') && ($field_name == 'zip') )
			{
				$value = str_replace('-', ' ', $value);
			}
			elseif ( ($section_name == 'signature') && ($field_name == 'signed') )
			{

				$components = array('first', 'middle', 'last', 'suffix');
				$value = '/s/';
				foreach ($components as $component)
				{
					$value .= utf8_encode($values->name->$component) . ' ';
				}
				$value = trim($value);

			}

			/*
			 * If this field uses non-standard spacing or font sizes, add the style tags to
			 * make those changes.
			 */
			$style_attributes = array();
			$style_attributes[] = 'letter-spacing: ' . $field->letter_spacing . 'px;';
			$style_attributes[] = 'word-spacing: ' . $field->word_spacing . 'px;';
			$style_attributes[] = 'font-size: ' . $field->font_size . 'em;';
			$value = '<span style="' . implode(' ', $style_attributes) . '">'
					. $value . '</span>';

			// All Y coordinates are offset by 4mm when writing HTML instead of plain text.
			$field->coordinates->y = $field->coordinates->y - 4;
			$mpdf->WriteFixedPosHTML($value, $field->coordinates->x, $field->coordinates->y, 100, 100);

		}
	}
	else
	{
		
		$value = utf8_encode($values->$section_name);

		if ($section_name == 'assistance')
		{
			if ($value == TRUE) $value ='x';
			else $value = '';
		}
		$mpdf->WriteText($section->coordinates->x, $section->coordinates->y, $value);

	}

	/*
	 * Every application gets an "electronically signed" notice.
	 */
	$mpdf->WriteFixedPosHTML('<span style="letter-spacing: 0; word-spacing: 0; font-size: .75em;">(electronically signed)</span>',
			114, 232, 100, 100);

}

/*
 * Save the file to the applications directory, using its ID.
 */
$dir = 'applications/';
$mpdf->Output($dir . $ab_id . '.pdf', 'F');
