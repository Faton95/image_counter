$(document).ready(function(){
    get('http://localhost/task/main.php?request_type=getRandomNumber')
        .then(data => {
            $('#value').html(data);
            document.getElementById("imageBanner").src = `file:///Applications/MAMP/htdocs/task/src/${data}.jpg`;

            get(`http://localhost/task/main.php?request_type=increaseCount&image_id=${data}`)
                .then(d => {
                    console.log("data inside", d);
                })
                .catch(err => {
                    console.log("error", err);
                })
                .finally(() => {
                    infiniteCountUpdater(data);
                });

        })
        .catch(error => {
            console.log("error", error)
            document.getElementById("imageBanner").src = `file:///Applications/MAMP/htdocs/task/src/empty.jpg`;
        });
});

function infiniteCountUpdater(data) {
    get(`http://localhost/task/main.php?request_type=getCount&image_id=${data}`)
            .then(data => {
                $('#value').html(data);     
            })
            .catch(err => {
                console.log("Second error", err);
                clearTimeout(timer);
            });

    var timer = setTimeout(() => infiniteCountUpdater(data), 5000)
}

function get(url) {
   return new Promise((resolve, reject) => {
       $.ajax({
           url,
           type: "GET",
           success(data) {
               resolve(data);
           },
           error(xhr) {
               reject(xhr);
           }
       })
   }) 
}