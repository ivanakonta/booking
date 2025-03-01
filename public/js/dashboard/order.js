(function($) {
    /* "use strict" */


 var dzChartlist = function(){

	var screenWidth = $(window).width();

	var polarAreaCharts = function(){
		var options = {
			series: [42, 47, 52],
			chart: {
				width: 250,
				type: 'polarArea',
				sparkline: {
					enabled: true,
				},
			},
			labels: ['VIP', 'Reguler', 'Exclusive'],
			fill: {
				opacity: 1,
				colors: ['#ce490f', '#ffc977', '#7d49ea']
			},
			stroke: {
				width: 0,
				colors: undefined
			},
			yaxis: {
				show: false
			},
			legend: {
				position: 'bottom'
			},
			plotOptions: {
				polarArea: {
					rings: {
						strokeWidth: 0
					},
					spokes: {
						strokeWidth: 0
					},
				}
			},
			theme: {
				monochrome: {
					enabled: true,
					shadeTo: 'light',
					shadeIntensity: 0.6
				}
			}
		};

        var chart = new ApexCharts(document.querySelector("#polarAreaCharts"), options);
        chart.render();
	}


		return {
			init:function(){
			},



			load:function(){
				polarAreaCharts();
			},

			resize:function(){
			}
		}

	}();

	jQuery(document).ready(function(){
	});

	jQuery(window).on('load',function(){
		setTimeout(function(){
			dzChartlist.load();
		}, 1000);

	});

	jQuery(window).on('resize',function(){


	});

})(jQuery);