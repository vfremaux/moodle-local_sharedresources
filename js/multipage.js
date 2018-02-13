/*
<<<<<<< HEAD
<<<<<<< HEAD
Utilisation de l'objet XmlHttpRequest
*/
function multi(numpage,page,courseid,isediting,repo,nbrpage){
=======
 * Utilisation de l'objet XmlHttpRequest
 * TODO : Rewrite this in full jQuery mode.
 */
// jshint undef:false, unused:false
function multi(numpage, page, courseid, isediting, repo, nbrpage) {
>>>>>>> MOODLE_34_STABLE

   var ajaxRequest;

	try{
		// Opera 8.0+, Firefox, Safari
		ajaxRequest = new XMLHttpRequest();
	} catch (e){
		// Internet Explorer Browsers
		try{
			ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
		} catch (e) {
			try{
				ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
			} catch (e){
				// Something went wrong
				alert("Your browser broke!");
				return false;
			}
		}
	}
	// Create a function that will receive data sent from the server
	ajaxRequest.onreadystatechange = function(){
		if(ajaxRequest.readyState == 4){
			var ajaxDisplay = document.getElementById('resources');
			ajaxDisplay.innerHTML = ajaxRequest.responseText;
		}
	}
	for (i = 1 ; i <= nbrpage; i++) {
		arr = document.getElementsByName('page' + i);
		if(i == numpage){
			for (var elem = 0; elem < arr.length; elem++) { 
				arr[elem].style.fontSize = "16pt";
				arr[elem].style.color = "black";
			}
		} else {
			for (var elem = 0; elem < arr.length; elem++) { 
				arr[elem].style.fontSize = "12pt";
				arr[elem].style.color = "grey";
			}
		}
	}
	ajaxRequest.open("POST", "ajax/pagemulti.php", true);
	var data = "numpage=" + numpage + "&page=" + page +"&courseid=" + courseid  +"&isediting=" + isediting +"&repo=" + repo;
	ajaxRequest.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	ajaxRequest.send(data); 
=======
 * Utilisation de l'objet XmlHttpRequest
 * TODO : Rewrite this in full jQuery mode.
 */
// jshint undef:false, unused:false
function multi(numpage, page, courseid, isediting, repo, nbrpage) {

   var ajaxRequest;

    try{
        // Opera 8.0+, Firefox, Safari.
        ajaxRequest = new XMLHttpRequest();
    } catch (e) {
        // Internet Explorer Browsers.
        try {
            ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (e) {
            try {
                ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
            } catch (e) {
                // Something went wrong.
                alert("Your browser broke!");
                return false;
            }
        }
    }

    // Create a function that will receive data sent from the server.

    ajaxRequest.onreadystatechange = function() {
        if (ajaxRequest.readyState == 4) {
            var ajaxDisplay = document.getElementById('resources');
            ajaxDisplay.innerHTML = ajaxRequest.responseText;
        }
    }
    for (i = 1; i <= nbrpage; i++) {
        arr = document.getElementsByName('page' + i);
        if (i == numpage) {
            for (var elem = 0; elem < arr.length; elem++) { 
                arr[elem].style.fontSize = "16pt";
                arr[elem].style.color = "black";
            }
        } else {
            for (var elem = 0; elem < arr.length; elem++) { 
                arr[elem].style.fontSize = "12pt";
                arr[elem].style.color = "grey";
            }
        }
    }
    ajaxRequest.open("POST", "ajax/pagemulti.php", true);
    var data = "numpage=" + numpage + "&page=" + page +"&courseid=" + courseid  +"&isediting=" + isediting +"&repo=" + repo;
    ajaxRequest.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    ajaxRequest.send(data); 
>>>>>>> MOODLE_33_STABLE
}