/********************************************************\
               Global RHQ Javascript File
                         v1.0

    All of the scripts are var-ized to keep it modular.
\********************************************************/

/********************************************************\
* Javascript Countdown
* Copyright (c) 2009 Markus Hedlund
* Version 1.1
* Licensed under MIT license
* http://www.opensource.org/licenses/mit-license.php
* http://labs.mimmin.com/countdown
\********************************************************/
var remaining = {
	getSeconds: function(target) {
		var today  = new Date();
		
		if (typeof(target) == 'object') {
			var targetDate = target;
		} else {
			var matches = target.match(/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2})(:(\d{2}))?/);   // YYYY-MM-DD HH-MM-SS
			if (matches != null) {
				matches[7] = typeof(matches[7]) == 'undefined' ? '00' : matches[7];
				var targetDate = new Date(matches[1], matches[2] - 1, matches[3], matches[4], matches[5], matches[7]);
			} else {
				var targetDate = new Date(target);
			}
		}
		
		return Math.floor((targetDate.getTime() - today.getTime()) / 1000);
	},

	getString: function(seconds, i18n, onlyLargestUnit, hideEmpty) {
		if (seconds < 1) {
			return '';
		}
		
		if (typeof(hideEmpty) == 'undefined' || hideEmpty == null) {
			hideEmpty = true;
		}
		if (typeof(onlyLargestUnit) == 'undefined' || onlyLargestUnit == null) {
			onlyLargestUnit = false;
		}
		if (typeof(i18n) == 'undefined' || i18n == null) {
			i18n = {
				weeks: ['week', 'weeks'],
				days: ['day', 'days'],
				hours: ['hour', 'hours'],
				minutes: ['minute', 'minutes'],
				seconds: ['second', 'seconds']
			};
		}
		
		var units = {
			weeks: 7*24*60*60,
			days: 24*60*60,
			hours: 60*60,
			minutes: 60,
			seconds: 1
		};
		
		var returnArray = [];
		var value;
		for (unit in units) {
			value = units[unit];
			if (seconds / value >= 1 || unit == 'seconds' || !hideEmpty) {
				secondsConverted = Math.floor(seconds / value);
				var i18nUnit = i18n[unit][secondsConverted == 1 ? 0 : 1];
				returnArray.push(secondsConverted + ' ' + i18nUnit);
				seconds -= secondsConverted * value;
				
				if (onlyLargestUnit) {
					break;
				}
			}
		};
		
		return returnArray.join(', ');
	},
	
	getStringDigital: function(seconds) {
		if (seconds < 1) {
			return '';
		}
		
		remainingTime = remaining.getArray(seconds);
		
		for (index in remainingTime) {
			remainingTime[index] = remaining.padNumber(remainingTime[index]);
		};
		
		return remainingTime.join(':');
	},

	getArray: function(seconds) {
		if (seconds < 1) {
			return [];
		}
		
		var units = [60*60, 60, 1];
		
		var returnArray = [];
		var value;
		for (index in units) {
			value = units[index];
			secondsConverted = Math.floor(seconds / value);
			returnArray.push(secondsConverted);
			seconds -= secondsConverted * value;
		};
		
		return returnArray;
	},

	padNumber: function(number) {
		return (number >= 0 && number < 10) ? '0' + number : number;
	}
};


/********************************************************\
* qTip
* Copyright (c) 2006 Craig Erskine
* Version 1.3
* Licensed under GNU License
* http://qrayg.com
\********************************************************/
var qTipTag = "a,label,input,img";

var ToolTip = {
	name: "qTip", offsetX: 8, offsetY: 15, tip: null,

	init: function () {
		var tipNameSpaceURI = "http://www.w3.org/1999/xhtml";
		if(!tipContainerID){ var tipContainerID = "qTip";}
		var tipContainer = document.getElementById(tipContainerID);

		if(!tipContainer) {
		  tipContainer = document.createElementNS ? document.createElementNS(tipNameSpaceURI, "div") : document.createElement("div");
		  tipContainer.setAttribute("id", tipContainerID);
		  document.getElementsByTagName("body").item(0).appendChild(tipContainer);
		}

		if (!document.getElementById) return;
		this.tip = document.getElementById (this.name);
		if (this.tip) document.onmousemove = function (evt) {ToolTip.move (evt)};

		var a, sTitle, elements;
		
		var elementList = qTipTag.split(",");
		for(var j = 0; j < elementList.length; j++)
		{	
			elements = document.getElementsByTagName(elementList[j]);
			if(elements)
			{
				for (var i = 0; i < elements.length; i ++)
				{
					a = elements[i];
					sTitle = a.getAttribute("title");				
					if(sTitle)
					{
						a.setAttribute("tiptitle", sTitle);
						a.removeAttribute("title");
						a.removeAttribute("alt");
						a.onmouseover = function() {ToolTip.show(this.getAttribute('tiptitle'))};
						a.onmouseout = function() {ToolTip.hide()};
					}
				}
			}
		}
	},

	move: function (evt) {
		var x=0, y=0;
		if (document.all) {//IE
			x = (document.documentElement && document.documentElement.scrollLeft) ? document.documentElement.scrollLeft : document.body.scrollLeft;
			y = (document.documentElement && document.documentElement.scrollTop) ? document.documentElement.scrollTop : document.body.scrollTop;
			x += window.event.clientX;
			y += window.event.clientY;
			
		} else {//Good Browsers
			x = evt.pageX;
			y = evt.pageY;
		}
		this.tip.style.left = (x + this.offsetX) + "px";
		this.tip.style.top = (y + this.offsetY) + "px";
	},

	show: function (text) {
		if (!this.tip) return;
		this.tip.innerHTML = text;
		this.tip.style.display = "block";
	},

	hide: function () {
		if (!this.tip) return;
		this.tip.innerHTML = "";
		this.tip.style.display = "none";
	},
};

window.onload = function () {
	ToolTip.init();
}