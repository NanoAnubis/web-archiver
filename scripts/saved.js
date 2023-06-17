document.addEventListener("DOMContentLoaded", function() {
    fetch("getwebsites.php")
      .then(response => response.json())
      .then(data => {
        const websiteTable = document.getElementById("websiteTable");
  
        data.forEach(website => {
          const row = document.createElement("tr");
          row.innerHTML = `
            <td><a href="${website.url}">${website.url}</a></td>
            <td>${website.date}</td>
            <td><a href="${website.dir}">${website.dir}</a></td>
          `;
          websiteTable.querySelector("tbody").appendChild(row);
        });
      })
      .catch(error => console.error(error));
  });