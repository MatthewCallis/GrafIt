<?php

$plugin_info = array(
	'pi_name' => 'Grafit',
	'pi_version' => '1.0',
	'pi_author' => 'Matthew Callis, based on work by <a href="http://intepid.com/">Mark Pursey</a>',
	'pi_author_url' => 'http://paramoreredd.com/',
	'pi_description' => 'Grafit - Simple sprakline style graphs from CSV style data',
	'pi_usage' => Grafit::usage()
);

class Grafit{
	var $return_data;
	function Grafit($str = ''){
		global $TMPL;
		$content = $TMPL->tagdata;
		$data = explode(",", $content);
		$total = count($data);

		$gap = (is_numeric($TMPL->fetch_param('gap')) ? $TMPL->fetch_param('gap') : 0.25);
		$gap *= 0.5;

		$bg = (($TMPL->fetch_param('bg') != '') ? sscanf($TMPL->fetch_param('bg'), "%6x", $color) : 0xffffff);
		$color = (($TMPL->fetch_param('color') != '') ? sscanf($TMPL->fetch_param('color'), "%6x", $color) : 0xf92672);

		$id = (($TMPL->fetch_param('id') != '') ? $TMPL->fetch_param('id') : 'myGraf');
		$height = (is_numeric($TMPL->fetch_param('height')) ? $TMPL->fetch_param('height') : '16');
		$height = (($height > 128) ? 128 : $height);
		$width_raw = $TMPL->fetch_param('width');
		$width = (is_numeric($width_raw) ? ($total * $width_raw) : ($total * 4));
		$width = (($width > 1024) ? 128 : $width);
		$width = (($width <= 0) ? ($height * 4) : $width);

		$output = (($TMPL->fetch_param('output') != '') ? $TMPL->fetch_param('output') : '');

		if($output == 'js'){
			$script = '<canvas id="'.$id.'" width="'.$width_raw.'" height="'.$height.'"></canvas>';
			$script .= '<script type="text/javascript">';
			$script .= 'var a=['.$content.'],b=Math.min.apply(Math,a),c=Math.max.apply(Math,a);b=0>b?b:0;var d=document.getElementById("'.$id.'"),e=d.height,f=d.width,g=d.getContext("2d");g.lineJoin="round";g.strokeStyle="#'.$color.'";g.lineWidth=2;g.beginPath();g.moveTo(0,e-(e-1)/(c-b)*a[0]-.5+b*((e-1)/(c-b)));g.shadowOffsetX=1;g.shadowOffsetY=1;g.shadowBlur=2;g.shadowColor="#bbb";for(var h=a.length,i=0;i<h;i++)g.lineTo(f/(h-1)*i,e-(e-1)/(c-b)*a[i]-.5+b*((e-1)/(c-b)));g.stroke();';
			$script .= '</script>';
			$this->return_data = $script;
		}
		else{
			$image = @imagecreatetruecolor($width, $height) or die("Couldn't initialize new GD image stream");
			$transparent_color = imagecolorallocate($image, 255, 255, 255);
			imagecolortransparent($image, $transparent_color);
			imagefilledrectangle($image, 0, 0, $width, $height, $transparent_color);

			$lower = $upper = $data[0];
			for($i = 1; $i < $total; $i++){
				if($lower > $data[$i]) $lower = $data[$i];
				else if($upper < $data[$i]) $upper = $data[$i];
			}

			if($lower == $upper){
				$upper += 0.5;
				$lower -= 0.5;
			}

			$zero = $height - (0 - $lower) / ($upper - $lower) * ($height - 2) - 1;

			for($i = 0; $i < $width; $i++){
				$x = $i * $total / $width;
				$f = $x - (int)$x;
				$y = $data[$x];

				if($gap && ($f < $gap || $f > 1-$gap)) continue;

				$v = $height - ($y - $lower) / ($upper - $lower) * ($height - 2) - 1;

				if($v <= $zero){
					$y1 = $v;
					$y2 = $zero;
				}
				else{
					$y2 = $v+1;
					$y1 = $zero+1;
				}
				imagefilledrectangle($image, $i, $y1, $i, $y2, $color);
			}
			ob_start();
			imagepng($image);
			imagedestroy($image);
			$encoded = base64_encode(ob_get_clean());
			$this->return_data = '<img src="data:image/png;base64,'.$encoded.'" id="'.$id.'" width="'.round($width).'" height="'.round($height).'" alt="'.$id.'"/>';
		}
	}

	function usage(){
		ob_start();
?>
		{exp:grafit gap="0.25" height="16" id="graph"}0,8,5,6,8,14,12,9,14,35,36,39,29,46,32,6,35,23{/exp:grafit}
		Will output the graph as an image the user can save, using PHP

		{exp:grafit width="72" height="24" id="chart" output="js"}0,8,5,6,8,14,12,9,14,35,36,39,29,46,32,6,35,23{/exp:grafit}
		Will output the graph as a canvas element the user cannot save, using JavaScript

		Parameters:
		bg: The background color of the graph, it will show transparent but when saved it will show; default is #FFFFFF
		color: The color of the bar/line; default is #F92672
		gap: The space between bars, in decimal percentage; default is 0.25
		width: a little tricky, on the bar it is the space of one bar AND gap, on a line graph is it the entire width of the chart; default is 4px
		height: the height of the graph in pixels; default is 16px
		output: the type of chart, either don't use it or 'js'; default is blank

<?php
		$buffer = ob_get_clean();
		return $buffer;
	}
}
?>