//Updates the currently shown admin pane tab
function updatePane(tab) {
	document.getElementById("rep-data").setAttribute("style", "display:none");
	document.getElementById("bill-data").setAttribute("style", "display:none");
	document.getElementById("vote-data").setAttribute("style", "display:none");
	document.getElementById("cat-data").setAttribute("style", "display:none");
	document.getElementById("val-data").setAttribute("style", "display:none");
	switch(tab) {
		case "rep":
			document.getElementById("rep-data").setAttribute("style", "display:block");
			break;
		case "bill":
			document.getElementById("bill-data").setAttribute("style", "display:block");
			break;
		case "vote":
			document.getElementById("vote-data").setAttribute("style", "display:block");
			break;
		case "cat":
			document.getElementById("cat-data").setAttribute("style", "display:block");
			break;
		case "val":
			document.getElementById("val-data").setAttribute("style", "display:block");
			break;
	}
}