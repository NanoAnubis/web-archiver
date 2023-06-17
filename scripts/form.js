document.getElementById("myForm").addEventListener("submit", function(event) {
    event.preventDefault(); // Prevent the form from submitting normally

    // Get the form data
    var formData = new FormData(this);

    console.log("Archiving started");

    // Create an AJAX request
    var xhr = new XMLHttpRequest();
    xhr.open("POST", this.action, true);
    xhr.send(formData);

    // Optional: Handle response if needed
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            // Handle the response here
            alert("Website archiving completed!");
            console.log(xhr.responseText);
        }
    };
});