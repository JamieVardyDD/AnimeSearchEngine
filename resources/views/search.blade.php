<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scan Anime Image for Search</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.0.1/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-gray-100">

<div class="container mx-auto mt-10">
    <div class="bg-white p-6 rounded-lg shadow-lg">
        <h2 class="text-2xl mb-5">Upload or Scan Anime Image for Search</h2>

        <!-- Image Upload Input -->
        <div>
            <label for="imageFile" class="block text-gray-700">Upload Image:</label>
            <input type="file" id="imageFile" accept="image/*" class="w-full p-2 border border-gray-300 rounded mt-2 mb-4">
        </div>

        <!-- Scan Image Button -->
        <div>
            <button id="scanImageButton" class="bg-blue-500 text-white px-4 py-2 rounded mb-4">Scan Image using Camera</button>
            <button id="closeScanButton" class="bg-red-500 text-white px-4 py-2 rounded mb-4 hidden">Close Scan</button>
            <video id="video" width="320" height="240" autoplay class="hidden border"></video>
            <canvas id="canvas" width="320" height="240" class="hidden"></canvas>
        </div>

        <!-- Pop-out Image after Scan -->
        <div id="scannedImageContainer" class="mt-4 hidden">
            <h3 class="text-xl mb-3">Scanned Image:</h3>
            <img id="scannedImage" class="border rounded-lg max-w-full h-auto">
        </div>

        <!-- Search Button -->
        <button id="searchImage" class="bg-green-500 text-white px-4 py-2 rounded mt-4">Search Anime</button>
    </div>
</div>

<!-- Result Section -->
<div class="container mx-auto mt-10 bg-white p-6 rounded-lg shadow-lg">
    <h2 class="text-2xl mb-5">Search Results</h2>
    <div id="searchResults" class="mt-5">
        <!-- Anime details will appear here -->
    </div>
</div>

<script>
    $(document).ready(function() {
        let video = document.getElementById('video');
        let canvas = document.getElementById('canvas');
        let context = canvas.getContext('2d');
        let scanning = false;
        let stream = null;
        let imageBlob = null;

        // Ensure CSRF token is included in all AJAX requests
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Start the camera stream
        $('#scanImageButton').on('click', function(e) {
            e.preventDefault();
            scanning = true;
            $('#video').removeClass('hidden');
            $('#closeScanButton').removeClass('hidden');
            $('#scanImageButton').addClass('hidden');

            navigator.mediaDevices.getUserMedia({ video: true })
            .then(function(mediaStream) {
                stream = mediaStream;
                video.srcObject = stream;
            })
            .catch(function(err) {
                console.error('Error accessing the camera: ', err);
            });
        });

        // Stop the camera stream
        $('#closeScanButton').on('click', function(e) {
            e.preventDefault();
            scanning = false;

            if (stream) {
                let tracks = stream.getTracks();
                tracks.forEach(function(track) {
                    track.stop();
                });
            }

            $('#video').addClass('hidden');
            $('#closeScanButton').addClass('hidden');
            $('#scanImageButton').removeClass('hidden');
        });

        // Capture the image from the video stream
        $('#scanImageButton').on('click', function(e) {
            e.preventDefault();

            setTimeout(() => {
                context.drawImage(video, 0, 0, canvas.width, canvas.height);
                
                canvas.toBlob(function(blob) {
                    let scannedImageURL = URL.createObjectURL(blob);
                    $('#scannedImage').attr('src', scannedImageURL);
                    $('#scannedImageContainer').removeClass('hidden');
                    imageBlob = blob;
                });
            }, 1000);
        });

        // Handle the search button click
        $('#searchImage').on('click', function(e) {
            e.preventDefault();

            let imageFile = $('#imageFile')[0].files[0];
            let formData = new FormData();
            
            if (imageBlob) {
                formData.append('image', imageBlob, 'scanned-image.png');
            } else if (imageFile) {
                formData.append('image', imageFile);
            } else {
                alert('Please upload or scan an image to search.');
                return;
            }

            // Send AJAX request to the backend
            $.ajax({
                url: '/search-anime',  // Laravel route
                type: 'POST',
                data: formData,
                processData: false,  // Do not process the data
                contentType: false,  // Don't set content type automatically
                success: function(response) {
                    // Handle the Trace.moe API response
                    if (response && response.result && response.result.length > 0) {
                        let result = response.result[0];
                        let animeTitle = result.anilist.title.romaji || "Unknown";
                        let episode = result.episode || "N/A";
                        let timestamp = result.from || "N/A";
                        let animeImageURL = result.image;

                        // Display the search results below the page
                        $('#searchResults').html(`
                            <div class="p-4 bg-gray-200 mb-4 rounded-lg">
                                <p class="text-green-500 font-semibold">Anime Title: ${animeTitle}</p>
                                <p><strong>Episode:</strong> ${episode}</p>
                                <p><strong>Timestamp:</strong> ${new Date(timestamp * 1000).toISOString().substr(11, 8)}</p>
                                <p><strong>Similarity:</strong> ${(result.similarity * 100).toFixed(2)}%</p>
                                <img src="${animeImageURL}" alt="Anime scene" class="mt-2 rounded shadow-lg">
                            </div>
                        `);
                    } else {
                        $('#searchResults').html('<p class="text-red-500">No matching results found.</p>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error during the API call:', error);
                    $('#searchResults').html(`<p class="text-red-500">Error: ${xhr.responseText}</p>`);
                }
            });
        });
    });
</script>
</body>
</html>
