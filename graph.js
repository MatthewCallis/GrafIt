var values = ['.$content.'];
var min = Math.min.apply(Math, values);
var max = Math.max.apply(Math, values);
min = (0 > min ? min : 0);
var canvas = document.getElementById("'.$id.'");
var ch = canvas.height;
var cw = canvas.width;
var context = canvas.getContext("2d");
context.lineJoin = "round";
context.strokeStyle = "#'.$color.'";
context.lineWidth = 2;
context.beginPath();
context.moveTo(0, (ch - (((ch - 1) / (max - min)) * values[0]) - .5 + (min * ((ch - 1) / (max - min)))));
context.shadowOffsetX = 1;
context.shadowOffsetY = 1;
context.shadowBlur = 2;
context.shadowColor = "#bbb";
var valueLength = values.length;
for(var i = 0; i < valueLength; i++){
	context.lineTo(((cw / (valueLength - 1)) * i), (ch - (((ch - 1) / (max - min)) * values[i]) - .5 + (min * ((ch - 1) / (max - min)))));
}
context.stroke();