/*

*/
// Navbar dropdown
var dropdowns = document.querySelectorAll("nav ul li a:not(:only-child)");
dropdowns.forEach(function (elem) {
	elem.addEventListener("click", function (e) {
		var dropdownuls = document.querySelectorAll(".nav-dropdown");
		dropdownuls.forEach(function (el) {
			el.style.display = "none";
		});
		var ddul = this.nextElementSibling;
		if (ddul.style.display === "block") {
			ddul.style.display = "none"
		} else {
			ddul.style.display = "block"
		}
		e.stopPropagation();
	});
});
document.getElementsByTagName("html")[0].addEventListener("click", function () {
	var dropdownuls = document.querySelectorAll(".nav-dropdown");
	dropdownuls.forEach(function (el) {
		el.style.display = "none";
	});
});

// Pagination goto
var paginator_goto = document.querySelector("#pgn-goto");
if (paginator_goto !== null) {
	paginator_goto.addEventListener('change', function () {
		location = this.value;
	});
};

// Close button
var close = document.querySelectorAll(".flash .close");
close.forEach(function (el) {
	el.addEventListener("click", function () {
		this.parentElement.style.display = "none";
	});
});
