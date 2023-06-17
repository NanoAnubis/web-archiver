document.addEventListener("DOMContentLoaded", function() {
    fetch("getwebsites.php")
      .then(response => response.json())
      .then(data => {
        const websiteTable = document.getElementById("websiteTable");
  
        data.forEach(website => {
          const row = document.createElement("tr");
          row.innerHTML = `
            <td><a href="${website.url}" target="_blank">${website.url}</a></td>
            <td>${website.date}</td>
            <td><a href="${website.dir}" target="_blank">Click here!</a></td>
            <td>${website.mode}</td>
          `;
          websiteTable.querySelector("tbody").appendChild(row);
        });
      })
      .catch(error => console.error(error));
  });