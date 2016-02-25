/**
*
** Cuir.js **
* Easy include file for HTML and Mustache.js
*
* Description [fr]:
*     Cuir.js permet de faire des includes de page html dans des pages html
*     ceci dans le but de pouvoir parser son intégration html en plusieurs fichiers.
*
*     Cuir.js est également adapté au moteur de template Mustache afin de faire passer
*     simplement les datas de Mustache d'un include à l'autre.
*
*
* Use: 
*     <script type="text/javascript" src="cuir.js"></script>
*     <script>$(document).ready(function(){var data = {data1:"dataNo1"}; $("html").cuir(data);});</script>
*     <script type="include" src="path/to/my/file.html" data="json_serialise(ObjData)"></script>
*
*
* Hack google Chrome:
*	  lancer Chrome with: "path/to/chrome.exe" --allow-file-access-from-files
*     afin qu'il autorise les requetes ajax sur file:///*
*
*
* Author: Raphaël Perraudeau
*
* Require: jquery
*
* Version:0.3b
* 
*/

var cuir_increment = 0;

$.fn.cuir = function(globalData){
	globalData = globalData || {};
	$(this).find("script[type='include']").each(function(){
		var self = this;
		if($(this).attr("data")){
			globalData = jQuery.extend({}, JSON.parse($(this).attr("data")), globalData);
		}
		var className = false;
		if($(this).attr("class")){
			className = $(this).attr("class");
		}
		$.ajax({
			type: "GET",
			url: $(this).attr("src"),
			dataType:'text',
			async: false,
			error: function(e){
				console.log(e);
			},
			success: function(xml){
				cuir_increment+=1;
				var tmpIncr = cuir_increment;
				
				if(typeof Mustache!=="undefined" && Mustache!==false){
					xml = Mustache.render(xml, globalData);
				}
				
				if(className!==false){
					xml = $(xml).addClass(className);
					xml = $(xml).wrap("<div />").parent().html();
				}
				
				$(self).replaceWith("<div type='text' class='new"+tmpIncr+"'>"+xml+"</div>")
				
				
				
				$('.new'+tmpIncr).cuir(globalData);
				
				$('.new'+tmpIncr).replaceWith($('.new'+tmpIncr).html())
			}
		});
	});
}