function validateForm() {
    var a = document.forms["form"]["Provider_id"].value;
    var b = document.forms["form"]["type_of_competency"].value;
    var c = document.forms["form"]["last_training_date"].value;
    var d = document.forms["form"]["type_of_training"].value;
    var e = document.forms["form"]["length_of_training"].value;
    var f = document.forms["form"]["training_organization_id"].value;
    var g = document.forms["form"]["facilitator"].value;
    
    var elmt = document.getElementById("Tester  Name");
    var elmt2 = document.getElementById("Type of Competency");
    var elmt3 = document.getElementById("date");
    var elmt4 = document.getElementById("Type of Activity/Training");
    var elmt5 = document.getElementById("Length of Activity/Training");
    var elmt6 = document.getElementById("Training Organization");
    var elmt7 = document.getElementById("Name of Facilitator(s)");
    
    
     if (a == null || a == "")
    {
        elmt.style.boxShadow = "2px 2px 10px rgba(200, 0, 0, 0.85)";
        alert("Please enter the ''" + elmt.id + "''");
        return false;
    }
    if (b == null || b == "") {
        elmt2.style.boxShadow = "2px 2px 10px rgba(200, 0, 0, 0.85)";
        alert("Please enter the ''" + elmt2.id + "''");
        return false;
    }

    if (c == null || c == "")
    {
        elmt3.style.boxShadow = "2px 2px 10px rgba(200, 0, 0, 0.85)";
        alert('Please enter the  "Date of Last Training/Activity"');
        return false;
    }
    
    if (d == null || d == "" )
    {
        elmt4.style.boxShadow = "2px 2px 10px rgba(200, 0, 0, 0.85)";
        alert("Please enter the ''" + elmt4.id + "''");;
        return false;
    }

    if (e == null || e == "")
    {
        elmt5.style.boxShadow = "2px 2px 10px rgba(200, 0, 0, 0.85)";
        alert("Please enter the ''" + elmt5.id + "''");;
        return false;
    }

   
    if (f == null || f == "") {
        elmt6.style.boxShadow = "2px 2px 10px rgba(200, 0, 0, 0.85)";
        alert("Please enter the ''" + elmt6.id + "''");
        return false;
    }
    
     if (g == null || g == "") {
        elmt7.style.boxShadow = "2px 2px 10px rgba(200, 0, 0, 0.85)";
        alert("Please enter the ''" + elmt7.id + "''");
        return false;
    }

    
    
}

function emptyInput(input) {

    input.style.boxShadow = 'none';
}



$(document).ready(function () {

    $("#date").datepicker(
            {
                showButtonPanel: true
                , dateFormat: 'yy/mm/dd'
                , dayNamesMin: ['Mon', 'Tues', 'Wed', 'Thur', 'Fri', 'Sat', 'Sun']
                , dayNames: ['Sunday', 'Monday', 'Tuesday', 'Wednesday', ' Thursday ', 'Friday', 'Saturday']
                , monthNamesShort: ['Jan', 'Fed', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
                , monthNames: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December', ]
                , prevText: 'Previous'
                , nextText: 'Next'
                , closeText: 'OK'
                , currentText: "Today"
            });

}); //EOf:: DOM isReady

$(document).ready(function () {

    $("#date2").datepicker(
            {
                showButtonPanel: true
                , dateFormat: 'yy/mm/dd'
                , dayNamesMin: ['Mon', 'Tues', 'Wed', 'Thur', 'Fri', 'Sat', 'Sun']
                , dayNames: ['Sunday', 'Monday', 'Tuesday', 'Wednesday', ' Thursday ', 'Friday', 'Saturday']
                , monthNamesShort: ['Jan', 'Fed', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
                , monthNames: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December', ]
                , prevText: 'Previous'
                , nextText: 'Next',
                closeText: 'OK'
                , currentText: "Today"
            });

}); //EOf:: DOM isReady
