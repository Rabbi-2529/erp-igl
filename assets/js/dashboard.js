  $(function() {
        "use strict";
    $('#alldata').datepicker({
        changeMonth: true,
        changeYear: true,
        showButtonPanel: true,
        maxDate: "+0M",
        dateFormat: 'MM yy'
    }).focus(function() {
        var thisCalendar = $(this);
        $('.ui-datepicker-calendar').detach();
        $('.ui-datepicker-close').click(function() {
var month = $("#ui-datepicker-div .ui-datepicker-month :selected").val();
var year = $("#ui-datepicker-div .ui-datepicker-year :selected").val();
thisCalendar.datepicker('setDate', new Date(year, month, 1));
        });
    });
});

// Chart colours follow Theme Colors: the default theme keeps the validated logo palette,
// a company theme uses its own main / highlight colours (read from the page's CSS variables).
function erpThemeColor(name, fallback) {
    var v = (getComputedStyle(document.documentElement).getPropertyValue(name) || '').trim().toLowerCase();
    return /^#[0-9a-f]{6}$/.test(v) ? v : fallback;
}
function erpTint(hex, amount) {
    var out = '#';
    for (var i = 1; i < 7; i += 2) {
        var c = parseInt(hex.substr(i, 2), 16);
        c = Math.round(c + (255 - c) * amount);
        out += (c < 16 ? '0' : '') + c.toString(16);
    }
    return out;
}
function erpRgba(hex, alpha) {
    return 'rgba(' + parseInt(hex.substr(1, 2), 16) + ', ' + parseInt(hex.substr(3, 2), 16) + ', ' + parseInt(hex.substr(5, 2), 16) + ', ' + alpha + ')';
}
var ERP_CHART = (function () {
    // "Previous style" on Theme Colors: the original ERP chart colours
    if ((getComputedStyle(document.documentElement).getPropertyValue('--erp-style') || '').trim() === 'classic') {
        return {
            classic: true,
            sales: '#008000', purchase: '#3e95cd',
            salesFill: 'rgba(0, 128, 0, 0.2)', purchaseFill: 'rgba(62, 149, 205, 0.2)',
            bar: 'rgba(0, 0, 0, 0.5)', barBorder: 'rgba(0, 0, 0, 1)', accent: '#000000',
            bestBar: undefined, bestBarBorder: undefined, pie: null
        };
    }
    // Dashboard colours: green, red, light blue, black (+ dark blue, light green), read from theme-felna.css
    var c = function (name, fallback) { return erpThemeColor('--erp-chart-' + name, fallback); };
    var colours = {
        sales: c('sales', '#12a852'), purchase: c('purchase', '#c4161f'), expense: c('expense', '#2b2f2c'),
        salary: c('salary', '#4fb3e8'), service: c('service', '#1c5cab'), collected: c('collected', '#8fdcae')
    };
    return {
        classic: false,
        colours: colours,
        // best-selling products, one colour per bar (every pair checked for colour blindness)
        categorical: [colours.sales, colours.purchase, colours.salary, colours.expense, colours.service],
        sales: colours.sales,
        purchase: colours.purchase,
        salesFill: erpRgba(colours.sales, 0.12),
        purchaseFill: erpRgba(colours.purchase, 0.08),
        bar: erpRgba(colours.sales, 0.85),
        barBorder: colours.sales,
        accent: colours.purchase,
        bestBar: erpRgba(colours.sales, 0.85),
        bestBarBorder: colours.sales,
        pie: [colours.sales, colours.purchase, colours.expense, colours.salary, colours.service]
    };
})();

  window.onload = function() {

    var currency = $("#currency").val();
    var totalsale = $("#totalsalep").val();
    var totalpurchase = $("#totalplurchasep").val();
    var totalexpense = $("#totalexpensep").val();
    var totalemployeesalary = $("#totalemployeesalaryp").val();
    var totalservice = $("#totalservicep").val();
// Logo red / green family, checked with the dataviz palette validator. Red and green are hard to
// tell apart with colour blindness, so every slice also keeps its name + value label beside it.
if (ERP_CHART.pie) { CanvasJS.addColorSet("felnaTech", ERP_CHART.pie); }
// Month summary from the dashboard (real figures for the chosen month; old hidden inputs as fallback)
var monthTitle = $("#dash-month-title").val();
var pieText = monthTitle ? monthTitle + " summary" : "Expense statement";
var erpDark = document.documentElement.getAttribute('data-theme') === 'dark';
var pieInk = erpDark ? "#e3e8e5" : "#1a1a1a";
var pieTitle = ERP_CHART.classic
    ? { text: pieText, fontColor: erpDark ? "#5fcf8d" : "green" }
    : { text: pieText, fontColor: pieInk, fontFamily: "Arial, Helvetica, sans-serif", fontWeight: "bold", fontSize: 18 };
var piePoints = null;
try { piePoints = JSON.parse($("#dash-month-summary").val() || "null"); } catch (e) { piePoints = null; }
// zero slices would pile their labels on one spot; the table under the chart still lists every figure
if (piePoints) { piePoints = piePoints.filter(function (p) { return Number(p.y) > 0; }); }
if (piePoints && !ERP_CHART.classic) { piePoints.forEach(function (p) { if (p.key && ERP_CHART.colours[p.key]) { p.color = ERP_CHART.colours[p.key]; } }); }
var pieOptions = {
    animationEnabled: true,
    backgroundColor: "transparent",
    title: pieTitle,
   
    data: [{
        type: "pie",
        startAngle: 240,
        yValueFormatString: currency ? "#,##0.00 " + currency : "TK #,##0.00",
        indexLabel: "{label} {y}",
        dataPoints: piePoints || [
            {y: totalsale, label: "Total Sale"},
             {y: totalpurchase, label: "Total Purchase"},
            {y: totalexpense, label: "Total Expense"},
            {y: totalemployeesalary, label: "Employee Salary"},
            {y: totalservice, label: "Service "}
        ]
    }]
};
if (erpDark) { pieOptions.data[0].indexLabelFontColor = pieInk; }
if (!ERP_CHART.classic) {
    pieOptions.colorSet = "felnaTech";
    pieOptions.data[0].indexLabelFontColor = pieInk;
}
if (document.getElementById("chartContainer")) {
    var chart = new CanvasJS.Chart("chartContainer", pieOptions);
    chart.render();
}

}

$(function (){
   "use strict"; 
              var mvar=$("#month").val();
              var splitmonth = mvar.substring(0, mvar.length - 1);
              var month = splitmonth.split(",");

              var tmsl=$("#tlvmonthsale").val();
              var splitsale = tmsl.substring(0, tmsl.length - 1);
              var sale = splitsale.split(",");


              var tmpurchase=$("#tlvmonthpurchase").val();
              var splitpurchase = tmpurchase.substring(0, tmpurchase.length - 1);
              var purchase = splitpurchase.split(",");
              var label = $("#salspurhcaselabel").val();

               var bestslabel = $("#bestsalelabel").val();
               var splitbslabel = bestslabel.substring(0, bestslabel.length - 1);
               var bestsalelabel = splitbslabel.split(",");

               var bestsdata  = $("#bestsaledata").val();
              var splitbsdata = bestsdata.substring(0, bestsdata.length - 1);
               var bestsaledata = splitbsdata.split(",");

               var bestsalmax    = $("#bestsalemax").val();

               // only on pages that have this chart (the dashboard draws its year chart in #yearlyreportall)
               if (document.getElementById("yearlyreport")) new Chart(document.getElementById("yearlyreport"), {
  type: 'line',
  data: {
    labels: month,
    datasets: [{ 
        data: sale,
        label: "Sales",
        borderColor: ERP_CHART.sales, // logo green (or the theme main colour)
        fill: false
      }, { 
        data: purchase,
        label: "Purchase",
        borderColor: ERP_CHART.purchase, // logo red, darker, so colour-blind readers can tell it from green
        fill: false
      }
    ]
  },
  options: {
    title: {
      display: true,
      text: label
    }
  }
});



  var bestslabel = $("#bestsalelabel").val();
               var splitbslabel = bestslabel.substring(0, bestslabel.length - 1);
               var bestsalelabel = splitbslabel.split(",");

               var bestsdata  = $("#bestsaledata").val();
              var splitbsdata = bestsdata.substring(0, bestsdata.length - 1);
               var bestsaledata = splitbsdata.split(",");

               var bestsalmax    = $("#bestsalemax").val();
   var ctx = document.getElementById("lineChart");
   if (!ctx) { return; } // the dashboard draws best sellers in #topSellingChart
    var myChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: bestsalelabel,
            datasets: [
                {
                    label: "Sales Product",
                    backgroundColor: ERP_CHART.bestBar, // logo green (or the theme main colour); unset in the previous style
                    borderColor: ERP_CHART.bestBarBorder,
                    borderWidth: 1,
                    fillColor: "#000000",
                    strokeColor: "#000000",
                    pointColor: "#000000",
                    pointStrokeColor: "#000000",
                    pointHighlightFill: "#000000",
                    pointHighlightStroke: "#000000",
                    maintainAspectRatio: false,
                    scaleFontColor: "#000000",
                    pointLabelFontColor: "#000000",
                    pointLabelFontSize: 30,
                    data: bestsaledata
                }

            ]
        },
        options: {
            responsive: true,
            tooltips: {
                mode: 'index',
                intersect: false
            },
            hover: {
                mode: 'nearest',
                intersect: true
            },
            scales: {
                xAxes: [{
                        display: true,
                        scaleLabel: {
                            display: true,
                            labelString: 'Products'
                        }
                    }],
                yAxes: [{
                        display: true,
                        ticks: {
                            beginAtZero: true,
                            steps: 10,
                            stepValue: 5,
                            max: Number(bestsalmax)
                        },
                        scaleLabel: {
                            display: true,
                            labelString: 'Quantity'
                        }
                    }]
            },
            "animation": {
                "duration": 1,
                "onComplete": function () {
                    var chartInstance = this.chart,
                            ctx = chartInstance.ctx;

                    ctx.color = '#000000';
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'bottom';

                    this.data.datasets.forEach(function (dataset, i) {
                        var meta = chartInstance.controller.getDatasetMeta(i);
                        meta.data.forEach(function (bar, index) {
                            var data = dataset.data[index];
                            ctx.fillText(data, bar._model.x, bar._model.y - 5);
                        });
                    });
                }
            }
        }


    });



   });


          
