<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\jui\DatePicker;
use yii\web\View;
use yii\bootstrap\Modal;

use app\views\helpers\Alert;

/* @var $this yii\web\View */
/* @var $model app\models\Product */
/* @var $form yii\widgets\ActiveForm */

//"Product type, MAS provider, geographical location
//Month/year -multiple  selection
//Geographic location- multiple selection"

//MAS provider,( multiple selection0 Product type
        //Month/year -multiple selection, Geographic location- multiple selection"
?>

<?php $form = ActiveForm::begin([
        'id' => 'cc-form'
]); ?>
    <div class="row">
       <div class="col-md-4">
            <?= $form->field($product, 'product_name')->dropDownList(
                   $productMap
                )
           ?>
        </div>
        
       <div class="col-md-4">
            <?= $form->field($product, 'product_type')->dropDownList(
                   $ptMap, 
                   array('options' => array(0=>array('selected'=>true)))
                )
           ?>
        </div>
        
        <div class="col-md-4">
            <div style="margin-top: 5px;" id='jqxZoneBox'></div>
            <div style="margin-top: 5px;" id='jqxStateBox'></div>
            <div style="margin-top: 5px;" id='jqxLGABox'></div>
        </div>
        
        <div class="col-md-4">
            <?= $form->field($model, 'created_date')->widget(DatePicker::classname(), [
                'language' => 'en',
                'dateFormat' => 'yyyy-MM-dd',
                'clientOptions' => ['changeYear' => true, 'changeMonth' => true],
                'options' => ['id'=>'from_date']
            ])->label('From') ?>
        </div>
        
    
        <div class="col-md-4">
            <?= $form->field($model, 'created_date')->widget(DatePicker::classname(), [
                'language' => 'en',
                'dateFormat' => 'yyyy-MM-dd',
                'clientOptions' => ['changeYear' => true, 'changeMonth' => true],
                'options' => ['id'=>'to_date']
            ])->label('To') ?>
        </div>
        
        <div class="col-md-12 marginbottom20 text-center">
            <?= Html::button('Filter', ['id'=>'filterButton',]); ?>
        </div>
        
    </div>
<?php ActiveForm::end(); ?>

<div class="row">
    <div class="col-md-1"></div>
    <div class="col-md-9" id="container" style="height: 400px; margin: 0 auto"></div>
    <div class="col-md-2"></div>
</div>

<?php
    Modal::begin([
        //'header' => '<h2>Hello world</h2>',
        //'toggleButton' => ['label' => 'click me'],
        'clientOptions' => ['backdrop' => 'static'],
        'options' => ['id'=>'myModal', 'class' => 'loading-modal text-center'],
        'size' => 'SIZE_SMALL'
    ]);

    echo '<div class="row">' . 
            '<div class="col-md-offset-4 col-md-4 gif-wrapper">' .
                Html::img('@web/images/loading.gif', ['alt' => 'Loading Image', 'width' => '32', 'height' => '32']) .
                '<br/>' .
                '<div class="bold">Loading...</div>' .
            '</div>' .
         '</div>';

    Modal::end();

    
?>


<?php
    
    //JQWIDGETS FILES
    $this->registerJsFile('@web/plugins/jqwidgets/jqwidgets/jqxcore.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
    $this->registerJsFile('@web/plugins/jqwidgets/jqwidgets/jqxbuttons.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
    $this->registerJsFile('@web/plugins/jqwidgets/jqwidgets/jqxscrollbar.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
    $this->registerJsFile('@web/plugins/jqwidgets/jqwidgets/jqxlistbox.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
    $this->registerJsFile('@web/plugins/jqwidgets/jqwidgets/jqxcombobox.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
    $this->registerJsFile('@web/plugins/jqwidgets/jqwidgets/jqxpanel.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
    $this->registerJsFile('@web/plugins/jqwidgets/jqwidgets/jqxcheckbox.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
    
    $this->registerJs("
            lh = $lh;
            usageData = $usageData;
            
            var zones = new Array();
            var states = new Array();
            var LGAs = new Array();
            
            //parse zones
            for(key in lh){
                gzObject = lh[key];
                zones.push({value: gzObject.id, label: gzObject.location_name});
            }
            

            zones = jqxArraySorter(zones);
            
            // Create a jqxComboBox
            $('#jqxZoneBox').jqxComboBox({source: zones, multiSelect: true, width: 200, height: 25});
            $('#jqxStateBox').jqxComboBox({source: states, multiSelect: true, width: 200, height: 25});
            $('#jqxLGABox').jqxComboBox({source: LGAs, multiSelect: true, width: 200, height: 25});
            
            drawChart(usageData);
            
            $('#filterButton').on('click', function(){
                $('#myModal').modal();
                data = {
                    product_type: $('#product-product_type').val(),
                    product_id: $('#product-product_name').val(),
                    geozones: JSON.stringify(extractJQXItemsValues($('#jqxZoneBox').jqxComboBox('getSelectedItems'))),
                    states: JSON.stringify(extractJQXItemsValues($('#jqxStateBox').jqxComboBox('getSelectedItems'))),
                    lgas: JSON.stringify(extractJQXItemsValues($('#jqxLGABox').jqxComboBox('getSelectedItems'))),
                    from_date: $('#from_date').val(),
                    to_date: $('#to_date').val()
                };
                

                $.ajax({
                    url: 'confirmed-counterfeits',
                    type: 'POST',
                    data: data,
                    success: function(jsonResponse){
                        drawChart(jsonResponse);
                        $('#myModal').modal('hide')
                    },
                    error: function(jqXHR, textStatus, errorThrown ){
                        console.log('jqXHR: '+JSON.stringify(jqXHR));
                        console.log('textStatus: '+textStatus);
                        console.log('errorThrown: '+errorThrown);
                    }
                });
            });


            function drawChart(usageData){
                log(usageData);
                
                Highcharts.chart('container', {
                    chart: {
                        type: 'column'
                    },
                    title: {
                        text: 'Confirmed counterfeit reports'
                    },
                    lang: {
                        noData: 'No data to display'
                    },
                    noData: {
                        style: {
                            fontWeight: 'normal',
                            fontSize: '15px',
                            textDecoration: 'underline',
                            color: '#363636'
                        }
                    },
                    subtitle: {
                        //text: 'Source: <a href=\"https://en.wikipedia.org/wiki/World_population\">Wikipedia.org</a>'
                    },
                    xAxis: {
                        categories:  Object.keys(usageData),
                        title: {
                            text: 'Geographic Locations',
                            align: 'middle',
                            offset: 40,
                            style: {'fontWeight': 'bold',  'color': '#363636'}
                        }
                    },
                    yAxis: {
                        min: 0,
                        max: 100,
                        lineWidth: 1,
                        tickWidth: 1,
                        title: {
                            text: 'Percentage MAS requests <br/>confirmed counterfeits',
                            align: 'middle',
                            offset: 60,
                            style: {'fontWeight': 'bold', 'color': '#363636'}
                        },
                        labels: {
                            format: '{value}%',
                            overflow: 'justify'
                        },
                    },
                    tooltip: {
                        valueSuffix: ' requests',
                        formatter: function (){
                            return this.x + ': ' + '<strong>' + this.y + '%' + '</strong>';
                        }
                    },
                    plotOptions: {
                        bar: {
                            dataLabels: {
                                enabled: true
                            }
                        }
                    },
                    legend: {
                        layout: 'vertical',
                        align: 'right',
                        verticalAlign: 'top',
                        x: -40,
                        y: 80,
                        floating: true,
                        borderWidth: 1,
                        backgroundColor: ((Highcharts.theme && Highcharts.theme.legendBackgroundColor) || '#FFFFFF'),
                        shadow: true,
                        enabled: false
                    },
                    credits: {
                        enabled: false
                    },
                    series: [{ data: Object.values(usageData)}]
                });
            }
            
            
        ",
        View::POS_READY,
        'cc-form-js'
    );    
    
    $this->registerJsFile(
        '@web/js/location-ops.js',
        ['depends' => [\yii\web\JqueryAsset::className()]]
    );
        
        
    $this->registerJs("
        (function() {
                        //set high charts global color scheme for all high charts instances on this page
                        Highcharts.setOptions({
                            colors: ['#2F9E69','#3366CC', '#DC3912', '#FF9900', '#109618', '#990099', '#0099C6', '#DD4477', '#AAAA11', '#B77322']
                        });
                    })();
                  ",
                View::POS_END,
                'highcharts-colors'
            );   
?>


<?php
    $this->registerJs("
            $('#reports-menu, #reports_counterfeits-report-menu').addClass('active');
            $('#reports_counterfeits-confirmed-menu').addClass('active2');
        ", 
        View::POS_LOAD,
        'per-menu'
    );
?>