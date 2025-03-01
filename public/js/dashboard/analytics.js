(function($) {
    /* "use strict" */


 var dzChartlist = function(){

	var screenWidth = $(window).width();

	var donutChart = function(){
		$("span.donut").peity("donut", {
			width: "80",
			height: "80"
		});
	}
	var peityLine = function(){
		$(".peity-line").peity("line", {
			fill: ["rgba(234, 73, 137, .0)"],
			stroke: '#ce490f',
			strokeWidth: '4',
			width: "100",
			height: "32"
		});
	}
	var chartCircle = function(){

		var optionsCircle = {
		  chart: {
			type: 'radialBar',
			//width:320,
			height: 350,
			offsetY: 0,
			offsetX: 0,

		  },
		  plotOptions: {
			radialBar: {
			  size: undefined,
			  inverseOrder: false,
			  hollow: {
				margin: 0,
				size: '30%',
				background: 'transparent',
			  },



			  track: {
				show: true,
				background: '#e1e5ff',
				strokeWidth: '10%',
				opacity: 1,
				margin: 15, // margin is in pixels
			  },


			},
		  },
		  responsive: [{
          breakpoint: 480,
          options: {
			chart: {
			offsetY: 0,
			offsetX: 0
		  },
            legend: {
              position: 'bottom',
              offsetX:0,
              offsetY: 0
            }
          }
        }],

		fill: {
          opacity: 1
        },

		colors:['#ce490f', '#ffb800', '#f35757'],
		series: [71, 63, 90],
		labels: ['New', 'Recover', 'In Treatment'],
		legend: {
			fontSize: '16px',
			show: false,
		  },
		}

		var chartCircle1 = new ApexCharts(document.querySelector('#chartCircle'), optionsCircle);
		chartCircle1.render();

	}

	var chartTimeline = function(){

		var optionsTimeline = {
			chart: {
				type: "bar",
				height: 400,
				stacked: true,
				toolbar: {
					show: false
				},
				sparkline: {
					//enabled: true
				},
				backgroundBarRadius: 5,
				offsetX: -10,
			},
			series: [
				 {
					name: "New Clients",
					data: [20, 40, 60, 35, 50, 70, 30]
				},
				{
					name: "Retained Clients",
					data: [-28, -32, -12, -5, -35, -10, -30]
				}
			],

			plotOptions: {
				bar: {
					columnWidth: "45%",
					endingShape: "rounded",
					colors: {
						backgroundBarColors: ['rgba(0,0,0,0)', 'rgba(0,0,0,0)', 'rgba(0,0,0,0)', 'rgba(0,0,0,0)', 'rgba(0,0,0,0)', 'rgba(0,0,0,0)', 'rgba(0,0,0,0)', 'rgba(0,0,0,0)', 'rgba(0,0,0,0)', 'rgba(0,0,0,0)'],
						backgroundBarOpacity: 1,
						backgroundBarRadius: 5,
						opacity:0
					},

				},
				distributed: true
			},
			colors:['#dd2f6e', '#3e4954'],

			grid: {
				show: true,
			},
			legend: {
				show: false
			},
			fill: {
				opacity: 1
			},
			dataLabels: {
				enabled: false,
				colors:['#dd2f6e', '#3e4954'],
				dropShadow: {
					enabled: true,
					top: 1,
					left: 1,
					blur: 1,
					opacity: 1
				}
			},
			xaxis: {
				categories: ['4', '5', '6', '7', '8', '9', '10'],
				labels: {
					style: {
						colors: '#787878',
						fontSize: '13px',
						fontFamily: 'Poppins',
						fontWeight: 400

					},
				},
				crosshairs: {
					show: false,
				},
				axisBorder: {
					show: false,
				},
			},

			yaxis: {
				//show: false
				labels: {
					style: {
						colors: '#787878',
						fontSize: '13px',
						fontFamily: 'Poppins',
						fontWeight: 400

					},
				},
			},

			tooltip: {
				x: {
					show: true
				}
			}
	};
		var chartTimelineRender =  new ApexCharts(document.querySelector("#chartTimeline"), optionsTimeline);
		 chartTimelineRender.render();
	}

	var chartBar = function(){
		var options = {
			  series: [
				{
					name: 'Net Profit',
					data: [75, 80, 65, 95, 42, 109, 100],
					//radius: 12,
				},

			],
				chart: {
				type: 'area',
				height: 350,
				toolbar: {
					show: false,
				},

			},
			plotOptions: {
			  bar: {
				horizontal: false,
				columnWidth: '55%',
				endingShape: 'rounded'
			  },
			},
			colors:['#ce490f'],
			dataLabels: {
			  enabled: false,
			},
			markers: {
		shape: "circle",
		},


			legend: {
				show: true,
				fontSize: '12px',

				labels: {
					colors: '#000000',

				},
				position: 'bottom',
				horizontalAlign: 'center',
				markers: {
					width: 19,
					height: 19,
					strokeWidth: 0,
					strokeColor: '#fff',
					fillColors: undefined,
					radius: 4,
					offsetX: 0,
					offsetY: 0
				}
			},
			stroke: {
			  show: true,
			  width: 0,
			  colors:['#ce490f'],
			},

			grid: {
				borderColor: '#eee',
			},
			xaxis: {

			  categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'July'],
			  labels: {
				style: {
					colors: '#3e4954',
					fontSize: '13px',
					fontFamily: 'Poppins',
					fontWeight: 100,
					cssClass: 'apexcharts-xaxis-label',
				},
			  },
			  crosshairs: {
			  show: false,
			  }
			},
			yaxis: {
				labels: {
			   style: {
				  colors: '#3e4954',
				  fontSize: '13px',
				   fontFamily: 'Poppins',
				  fontWeight: 100,
				  cssClass: 'apexcharts-xaxis-label',
			  },
			  },
			},
			fill: {
				type: 'solid',
				opacity: 0.8,
			},
			tooltip: {
			  y: {
				formatter: function (val) {
				  return "$ " + val + " thousands"
				}
			  }
			}
			};

			var chartBar1 = new ApexCharts(document.querySelector("#chartBar"), options);
			chartBar1.render();
	}

	/* Function ============ */
		return {
			init:function(){

			},


			load:function(){
				donutChart();
				peityLine();
				chartCircle();
				chartTimeline();
				chartBar();
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