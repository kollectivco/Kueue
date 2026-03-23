(function($) {
   
    if (jQuery("#WooCommerceEventsReport").length) {
        
        if( (typeof FooEventsReportsObj === "object") && (FooEventsReportsObj !== null) )
        {

            jQuery('.WooCommerceEventsDate').datepicker({

                showButtonPanel: true,
                closeText: FooEventsReportsObj.closeText,
                currentText: FooEventsReportsObj.currentText,
                monthNames: FooEventsReportsObj.monthNames,
                monthNamesShort: FooEventsReportsObj.monthNamesShort,
                dayNames: FooEventsReportsObj.dayNames,
                dayNamesShort: FooEventsReportsObj.dayNamesShort,
                dayNamesMin: FooEventsReportsObj.dayNamesMin,
                dateFormat: FooEventsReportsObj.dateFormat,
                firstDay: FooEventsReportsObj.firstDay,
                isRTL: FooEventsReportsObj.isRTL,

            });

        } else {

            jQuery('.WooCommerceEventsDate').datepicker();

        }
        
        var dateFrom = jQuery('#WooCommerceEventsDateFrom').val();
        var dateTo = jQuery('#WooCommerceEventsDateTo').val();
        var eventID = jQuery('#eventID').val();
        
        display_tickets_sold_data(dateFrom, dateTo, eventID);
        display_revenue_data(dateFrom, dateTo, eventID);
        display_net_revenue_data(dateFrom, dateTo, eventID);
        display_check_ins(dateFrom, dateTo, eventID);
        display_check_ins_today(dateFrom, dateTo, eventID);
        display_check_outs(dateFrom, dateTo, eventID);
        display_check_outs_today(dateFrom, dateTo, eventID);
        
        jQuery('.wrap').on('change', '#fooevents-include-canceled-tickets', function(e) {
        
            display_tickets_sold_data(dateFrom, dateTo, eventID);
        
        });
        
    }
    
    function display_check_ins_today(dateFrom, dateTo, eventID) {
        
        var dataVariations = {
            'action': 'fetch_check_ins_today',
            'dateFrom': dateFrom,
            'dateTo': dateTo,
            'eventID': eventID
        };
        
        jQuery.post(ajaxurl, dataVariations, function(response) {
           
            var check_ins = JSON.parse(response);
            var toolTipData = [];
            
            $.each(check_ins, function (index, value) {
                
                toolTipData.push({
                    meta : index, 
                    value : value
                });
                
            });

            var labels = Object.keys(check_ins);
            var series = Object.values(check_ins);

            var chart = new Chartist.Line('#fooevents-report-check-ins-today', {
                labels: labels,
                series: [toolTipData]
              }, {
                low: 0,
                fullWidth: true,
                axisX: {
                    showLabel: false,
                },
                axisY: {
                    onlyInteger: true
                },
                plugins: [
                  Chartist.plugins.tooltip({

                      anchorToPoint: true

                  })
                ]
              });
            
        });
    }
    
    function display_check_outs_today(dateFrom, dateTo, eventID) {
        
        var dataVariations = {
            'action': 'fetch_check_outs_today',
            'dateFrom': dateFrom,
            'dateTo': dateTo,
            'eventID': eventID
        };
        
        jQuery.post(ajaxurl, dataVariations, function(response) {
           
            var check_ins = JSON.parse(response);
            var toolTipData = [];
            
            $.each(check_ins, function (index, value) {
                
                toolTipData.push({
                    meta : index, 
                    value : value
                });
                
            });

            var labels = Object.keys(check_ins);
            var series = Object.values(check_ins);

            var chart = new Chartist.Line('#fooevents-report-check-outs-today', {
                labels: labels,
                series: [toolTipData]
              }, {
                low: 0,
                fullWidth: true,
                axisX: {
                    showLabel: false,
                },
                axisY: {
                    onlyInteger: true
                },
                plugins: [
                  Chartist.plugins.tooltip({

                      anchorToPoint: true

                  })
                ]
              });
            
        });
    }
    
    function display_check_ins(dateFrom, dateTo, eventID) {
        
        var dataVariations = {
            'action': 'fetch_check_ins',
            'dateFrom': dateFrom,
            'dateTo': dateTo,
            'eventID': eventID
        };
        
        jQuery.post(ajaxurl, dataVariations, function(response) {
            
            var check_ins = JSON.parse(response);
            var toolTipData = [];
            
            $.each(check_ins, function (index, value) {
                
                toolTipData.push({
                    meta : index, 
                    value : value
                });
                
            });

            var labels = Object.keys(check_ins);
            var series = Object.values(check_ins);
            
            var num_tickets = 0;
            
            $.each(series , function(index, val) { 
                num_tickets = parseInt(num_tickets) + parseInt(val);
            });
            
            jQuery('#WooCommerceCheckIns').html(num_tickets);

            var chart = new Chartist.Line('#fooevents-report-check-ins', {
                labels: labels,
                series: [toolTipData]
              }, {
                low: 0,
                fullWidth: true,
                axisX: {
                    showLabel: false,
                },
                axisY: {
                    onlyInteger: true
                },
                plugins: [
                  Chartist.plugins.tooltip({

                      anchorToPoint: true

                  })
                ]
              });
            
        });
        
    }
    
    function display_check_outs(dateFrom, dateTo, eventID) {
        
        var dataVariations = {
            'action': 'fetch_check_outs',
            'dateFrom': dateFrom,
            'dateTo': dateTo,
            'eventID': eventID
        };
        
        jQuery.post(ajaxurl, dataVariations, function(response) {
            
            var check_ins = JSON.parse(response);
            var toolTipData = [];
            
            $.each(check_ins, function (index, value) {
                
                toolTipData.push({
                    meta : index, 
                    value : value
                });
                
            });

            var labels = Object.keys(check_ins);
            var series = Object.values(check_ins);
            
            var num_tickets = 0;
            
            $.each(series , function(index, val) { 
                num_tickets = parseInt(num_tickets) + parseInt(val);
            });
            
            jQuery('#WooCommerceCheckOuts').html(num_tickets);

            var chart = new Chartist.Line('#fooevents-report-check-outs', {
                labels: labels,
                series: [toolTipData]
              }, {
                low: 0,
                fullWidth: true,
                axisX: {
                    showLabel: false,
                },
                axisY: {
                    onlyInteger: true
                },
                plugins: [
                  Chartist.plugins.tooltip({

                      anchorToPoint: true

                  })
                ]
              });
            
        });
        
    }
    
    function display_tickets_sold_data(dateFrom, dateTo, eventID) {
        
        var canceledTickets = '';
        if (jQuery('#fooevents-include-canceled-tickets').is(':checked')) {
            
            canceledTickets = 'yes';
            
        } else {
            
           canceledTickets = 'no'; 
            
        }
        
        var dataVariations = {
            'action': 'fetch_tickets_sold',
            'dateFrom': dateFrom,
            'dateTo': dateTo,
            'eventID': eventID,
            'canceledTickets': canceledTickets
        };
        
        jQuery.post(ajaxurl, dataVariations, function(response) {
            
            var tickets_sold_per_day = JSON.parse(response);
            var toolTipData = [];
            
            jQuery.each(tickets_sold_per_day, function (index, value) {
                
                toolTipData.push({
                    meta : index, 
                    value : value
                });
                
            });
            
            var labels = Object.keys(tickets_sold_per_day);
            var series = Object.values(tickets_sold_per_day);
            
            var num_tickets = 0;
            
            jQuery.each(series , function(index, val) { 
                num_tickets = parseInt(num_tickets) + parseInt(val);
            });
            
            jQuery('#WooCommerceTicketsSold').html(num_tickets);

            var chart = new Chartist.Line('#fooevents-report-tickets-sold', {
                labels: labels,
                series: [toolTipData]
              }, {
                low: 0,
                fullWidth: true,
                axisX: {
                    showLabel: false,
                },
                axisY: {
                    onlyInteger: true
                },
                plugins: [
                  Chartist.plugins.tooltip({

                      anchorToPoint: true

                  })
                ]
          });
              
        });
        
    }
    
    function display_net_revenue_data(dateFrom, dateTo, eventID) {
        
        var dataVariations = {
            'action': 'fetch_tickets_revenue_net',
            'dateFrom': dateFrom,
            'dateTo': dateTo,
            'eventID': eventID
        };
        
        jQuery.post(ajaxurl, dataVariations, function(response) {
            
            var tickets_revenue_per_day = JSON.parse(response);
            var toolTipData = [];
            
            $.each(tickets_revenue_per_day, function (index, value) {
                
                toolTipData.push({
                    meta : index, 
                    value : value
                });
                
            });

            var labels = Object.keys(tickets_revenue_per_day);
            var series = Object.values(tickets_revenue_per_day);
            
            var total_revenue = 0;
            
            $.each(series , function(index, val) { 
                total_revenue = parseInt(total_revenue) + parseInt(val);
            });
            
            var dataVariationsRev = {
                'action': 'fetch_revenue_formatted',
                'total_revenue': total_revenue,
            }
            
            jQuery.post(ajaxurl, dataVariationsRev, function(responseRev) {
                
                jQuery('#WooCommerceTicketsNetRevenue').html(responseRev);
                
            });
 
            var chart = new Chartist.Line('#fooevents-report-tickets-revenue-net', {
                labels: labels,
                series: [toolTipData]
              }, {
                low: 0,
                fullWidth: true,
                axisX: {
                    showLabel: false,
                },
                axisY: {
                    onlyInteger: true
                },
                plugins: [
                  Chartist.plugins.tooltip({
                      anchorToPoint: true,
                      currency: FooEventsReportsObj.currencySymbol
                  })
                ]
              });
            
        });
 
    }
    
    function display_revenue_data(dateFrom, dateTo, eventID) {
        
        var dataVariations = {
            'action': 'fetch_tickets_revenue',
            'dateFrom': dateFrom,
            'dateTo': dateTo,
            'eventID': eventID
        };
        
        jQuery.post(ajaxurl, dataVariations, function(response) {
            
            var tickets_revenue_per_day = JSON.parse(response);
            var toolTipData = [];
            
            $.each(tickets_revenue_per_day, function (index, value) {
                
                toolTipData.push({
                    meta : index, 
                    value : value
                });
                
            });

            var labels = Object.keys(tickets_revenue_per_day);
            var series = Object.values(tickets_revenue_per_day);
            
            var total_revenue = 0;
            
            $.each(series , function(index, val) { 
                total_revenue = parseInt(total_revenue) + parseInt(val);
            });
            
            var dataVariationsRev = {
                'action': 'fetch_revenue_formatted',
                'total_revenue': total_revenue,
            }
            
            jQuery.post(ajaxurl, dataVariationsRev, function(responseRev) {
                
                jQuery('#WooCommerceTicketsRevenue').html(responseRev);
                
            });
 
            var chart = new Chartist.Line('#fooevents-report-tickets-revenue', {
                labels: labels,
                series: [toolTipData]
              }, {
                low: 0,
                fullWidth: true,
                axisX: {
                    showLabel: false,
                },
                axisY: {
                    onlyInteger: true
                },
                plugins: [
                  Chartist.plugins.tooltip({
                      anchorToPoint: true,
                      currency: FooEventsReportsObj.currencySymbol
                  })
                ]
              });
            
        });
 
    }
    
})( jQuery );