<div 
    id="ashiful-upload-zone"
    class="border-2 border-dashed border-gray-300 p-6 rounded-lg text-center hover:bg-gray-50 transition-colors duration-200"
    ondragover="event.preventDefault(); this.classList.add('border-blue-500');"
    ondragleave="this.classList.remove('border-blue-500');"
    ondrop="handleDrop(event)"
>
    <div class="space-y-2">
        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        <div class="text-sm text-gray-600">
            <label for="file-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                <span>Upload a file</span>
                <input id="file-upload" name="file-upload" type="file" class="sr-only" onchange="handleFiles(this.files)">
            </label>
            <p class="pl-1">or drag and drop</p>
        </div>
        <p class="text-xs text-gray-500">
            PNG, JPG, GIF up to 10MB
        </p>
    </div>

    <!-- Preview Area -->
    <div id="preview-area" class="mt-4 space-y-4 hidden"></div>
</div>

<script>
    function handleDrop(e) {
        e.preventDefault();
        document.getElementById('ashiful-upload-zone').classList.remove('border-blue-500');
        handleFiles(e.dataTransfer.files);
    }

    function handleFiles(files) {
        const previewArea = document.getElementById('preview-area');
        previewArea.classList.remove('hidden');

        Array.from(files).forEach(file => {
            const fileId = Math.random().toString(36).substring(7);
            createPreviewElement(file, fileId);
            uploadFile(file, fileId);
        });
    }

    function createPreviewElement(file, fileId) {
        const previewArea = document.getElementById('preview-area');
        
        const wrapper = document.createElement('div');
        wrapper.id = `file-${fileId}`;
        wrapper.className = 'flex items-center space-x-4 bg-white p-3 rounded-lg shadow-sm border';
        
        // Image preview or icon
        let mediaPreview = '';
        if (file.type.startsWith('image/')) {
            const url = URL.createObjectURL(file);
            mediaPreview = `<img src="${url}" class="h-12 w-12 object-cover rounded-md">`;
        } else {
            mediaPreview = `<div class="h-12 w-12 bg-gray-100 rounded-md flex items-center justify-center text-gray-500 text-xs">${file.name.split('.').pop()}</div>`;
        }

        wrapper.innerHTML = `
            ${mediaPreview}
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 truncate">${file.name}</p>
                <div class="w-full bg-gray-200 rounded-full h-2.5 mt-2">
                    <div id="progress-${fileId}" class="bg-blue-600 h-2.5 rounded-full" style="width: 0%"></div>
                </div>
            </div>
            <div id="status-${fileId}" class="text-xs text-gray-500">Waiting...</div>
        `;

        previewArea.appendChild(wrapper);
    }

    function uploadFile(file, fileId) {
        const formData = new FormData();
        formData.append('file', file);
        
        const xhr = new XMLHttpRequest();
        xhr.open('POST', '{{ route("ashiful.upload.store") }}', true);
        xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');
        xhr.setRequestHeader('Accept', 'application/json');

        xhr.upload.onprogress = function(e) {
            if (e.lengthComputable) {
                const percentComplete = (e.loaded / e.total) * 100;
                document.getElementById(`progress-${fileId}`).style.width = percentComplete + '%';
                document.getElementById(`status-${fileId}`).innerText = Math.round(percentComplete) + '%';
            }
        };

        xhr.onload = function() {
            if (xhr.status === 200) {
                const data = JSON.parse(xhr.responseText);
                if (data.success) {
                    document.getElementById(`status-${fileId}`).innerHTML = `<a href="${data.url}" target="_blank" class="text-green-600 hover:underline">View</a>`;
                    document.getElementById(`progress-${fileId}`).classList.remove('bg-blue-600');
                    document.getElementById(`progress-${fileId}`).classList.add('bg-green-500');
                } else {
                    document.getElementById(`status-${fileId}`).innerText = 'Failed';
                    document.getElementById(`progress-${fileId}`).classList.add('bg-red-500');
                }
            } else {
                document.getElementById(`status-${fileId}`).innerText = 'Error';
                document.getElementById(`progress-${fileId}`).classList.add('bg-red-500');
            }
        };

        xhr.onerror = function() {
            document.getElementById(`status-${fileId}`).innerText = 'Network Error';
        };

        xhr.send(formData);
    }
</script>
