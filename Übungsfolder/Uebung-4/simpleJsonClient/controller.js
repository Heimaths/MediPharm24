//Starting point for JQuery init
$(document).ready(function () {
    $("#searchResult").hide();
    
    $("#btnSearchName").click(function (e) {
        searchPerson("queryPersonByName", $("#searchName").val());
    });

    $("#btnSearchDepartment").click(function (e) {
        searchPerson("queryPersonByDepartment", $("#searchDepartment").val());
    });

    $("#btnSearchEmail").click(function (e) {
        searchPerson("queryPersonByEmail", $("#searchEmail").val());
    });
});

function searchPerson(method, searchterm) {
    if (!searchterm) {
        alert("Bitte geben Sie einen Suchbegriff ein!");
        return;
    }

    $.ajax({
        type: "GET",
        url: "../serviceHandler.php",
        cache: false,
        data: {method: method, param: searchterm},
        dataType: "json",
        success: function (response) {
            $("#noOfentries").val(response.length);
            $("#resultTable").empty();
            
            response.forEach(function(person) {
                let p = person[0];
                $("#resultTable").append(
                    `<tr>
                        <td>${p.firstname}</td>
                        <td>${p.lastname}</td>
                        <td>${p.email}</td>
                        <td>${p.department}</td>
                    </tr>`
                );
            });
            
            $("#searchResult").show();
        },
        error: function(xhr, status, error) {
            alert("Ein Fehler ist aufgetreten: " + error);
        }
    });
}
