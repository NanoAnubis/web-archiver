document.addEventListener("DOMContentLoaded", function() {
    fetch("getwebsites.php")
      .then(response => response.json())
      .then(data => {
        const websiteTable = document.getElementById("websiteTable");
  
        data.forEach(website => {
          const row = document.createElement("tr");
          row.innerHTML = `
            <td>${website.url}</td>
            <td>${website.date}</td>
          `;
          websiteTable.querySelector("tbody").appendChild(row);
        });
      })
      .catch(error => console.error(error));
  });