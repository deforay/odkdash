function validateForm() {
    var a = document.forms["form"]["certification_issuer"].value;
    var elmt = document.getElementById("Certification Issued By");

    if (a == null || a == "")
    {
        elmt.style.boxShadow = "2px 2px 10px rgba(200, 0, 0, 0.85)";
        alert("Please enter the ''" + elmt.id + "''");
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