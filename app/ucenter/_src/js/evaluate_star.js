
function scoreFun(object, opts) {
	"use strict";
	var defaults = {
		fen_d: 24.5,
		ScoreGrade: 10,
		types: [
			"很不满意", 
			"差得太离谱，与卖家描述的严重不符，非常不满", 
			"不满意, 与卖家描述的不符，不满意", 
			"非常一般", 
			"一般", 
			"没有卖家描述的那么好", 
			"满意", 
			"很满意，与卖家描述的基本一致，还是挺满意的", 
			"非常满意", 
			"非常好，与卖家描述的完全一致，非常满意"
		],
		nameScore: ".fenshu",
		parent: ".star_score",
		attitude: ".attitude"
	};
	var options = $.extend({}, defaults, opts);
	var countScore = object.find(options.nameScore);
	var startParent = object.find(options.parent);
	var atti = object.find(options.attitude);
	var now_cli;
	var fen_cli;
	var atu;
	var fen_d = options.fen_d;
	var len = options.ScoreGrade;
		startParent.width(fen_d * len);
	var preA = (5 / len);
	for (var i = 0; i < len; i++) {
		var newSpan = $("<a href='javascript:void(0)'></a>");
		newSpan.css({
			"left": 0,
			"width": fen_d * (i + 1),
			"z-index": len - i
		});
		newSpan.appendTo(startParent)
	}
	startParent.find("a").each(function(index, element) {
		$(this).click(function() {
			now_cli = index;
			show(index, $(this));

		});
	});

	function show(num, obj) {
		var n = parseInt(num) + 1;
		var lefta = num * fen_d;
		var ww = fen_d * n;
		var scor = preA * n;
		var atu = options.types[parseInt(num)];
		object.find("a").removeClass("clibg");
		obj.addClass("clibg");
		obj.css({
			"width": ww,
			"left": "0"
		});
		countScore.text(scor);
		atti.text(atu)
		$('[name="value"]').val(scor);
	}
};

/**
 * 显示评价计分
 * @param  {[type]} ele [description]
 * @return {[type]}     [description]
 */
function showScoreFun(ele)
{
	//显示分数
	var num = $(ele).attr("tip");
	var w = num*2*24.5;//
	$(ele).css("width",w);
	$(ele).parent(".atar_show").siblings("span").text(num+"分");    
}

