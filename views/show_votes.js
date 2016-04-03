//Shows/Hides a senator's voting history
function showVotes(id) {
	if ($(id).css('display') == 'none') {
		$(id).css('display', 'table-row');
	} else {
		$(id).css('display','none');
	}
}