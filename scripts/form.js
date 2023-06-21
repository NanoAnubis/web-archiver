document.getElementById("myForm").addEventListener("submit", function(event) {
    event.preventDefault();

    var formData = new FormData(this);

    console.log("Archiving started");

    var xhr = new XMLHttpRequest();
    xhr.open("POST", this.action, true);
    xhr.send(formData);

    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            alert("Website archiving completed!");
            console.log(xhr.responseText);
        }
    };
});