var App = (function () {
  'use strict';
  
  App.dashboard = function( ){

    //Calendar Widget
    function calendar_widget(){
    	var widget = $(".widget-calendar");
    	var calNotes = $(".cal-notes", widget);
    	var calNotesDay = $(".day", calNotes);
    	var calNotesDate = $(".date", calNotes);

    	//Calculate the weekday name
    	var d = new Date();
			var weekday = new Array(7);
			weekday[0]=  "Sunday";
			weekday[1] = "Monday";
			weekday[2] = "Tuesday";
			weekday[3] = "Wednesday";
			weekday[4] = "Thursday";
			weekday[5] = "Friday";
			weekday[6] = "Saturday";

			var weekdayName = weekday[d.getDay()];

			calNotesDay.html( weekdayName );

			//Calculate the month name
			var month = new Array();
			month[0] = "January";
			month[1] = "February";
			month[2] = "March";
			month[3] = "April";
			month[4] = "May";
			month[5] = "June";
			month[6] = "July";
			month[7] = "August";
			month[8] = "September";
			month[9] = "October";
			month[10] = "November";
			month[11] = "December";

			var monthName = month[d.getMonth()];
			var monthDay = d.getDate();

			calNotesDate.html( monthName + " " + monthDay);

      if (typeof jQuery.ui != 'undefined') {
        $( ".ui-datepicker" ).datepicker({
        	onSelect: function(s, o){
        		var sd = new Date(s);
        		var weekdayName = weekday[sd.getDay()];
        		var monthName = month[sd.getMonth()];
						var monthDay = sd.getDate();

						calNotesDay.html( weekdayName );
						calNotesDate.html( monthName + " " + monthDay);
        	}
        });
      }
    }

    //Fullwidth line chart 1

    //World Map 1

	  //	calendar_widget();

      //world_map();

  };

  return App;
})(App || {});
