<template>
  <div 
    class="border-2 border-dashed p-6 rounded-lg text-center transition-colors duration-200"
    :class="{ 'border-blue-500 bg-blue-50': isDragging, 'border-gray-300': !isDragging }"
    @dragenter.prevent="isDragging = true"
    @dragleave.prevent="isDragging = false"
    @dragover.prevent
    @drop.prevent="handleDrop"
  >
    <div class="space-y-2">
      <div class="text-sm text-gray-600">
        <label class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500">
          <span>Upload a file</span>
          <input type="file" class="sr-only" @change="handleFileInput" multiple>
        </label>
        <span class="pl-1">or drag and drop</span>
      </div>
      <p class="text-xs text-gray-500">PNG, JPG, GIF up to 10MB</p>
    </div>

    <div v-if="previews.length > 0" class="mt-4 space-y-3 text-left">
      <div v-for="preview in previews" :key="preview.id" class="flex items-center space-x-4 bg-white p-3 rounded-lg shadow-sm border">
        <img v-if="preview.src" :src="preview.src" class="h-12 w-12 object-cover rounded-md">
        <div v-else class="h-12 w-12 bg-gray-100 rounded-md flex items-center justify-center text-gray-500 text-xs uppercase">{{ preview.file.name.split('.').pop() }}</div>
        
        <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-gray-900 truncate">{{ preview.file.name }}</p>
            <div class="w-full bg-gray-200 rounded-full h-2.5 mt-2">
                <div 
                    class="h-2.5 rounded-full" 
                    :class="{'bg-green-500': preview.status === 'success', 'bg-red-500': preview.status === 'error', 'bg-blue-600': preview.status === 'uploading' || preview.status === 'waiting'}"
                    :style="{ width: preview.progress + '%' }"
                ></div>
            </div>
        </div>
        <div class="text-xs text-gray-500">
            <a v-if="preview.status === 'success'" :href="preview.url" target="_blank" class="text-green-600 hover:underline">View</a>
            <span v-else>{{ preview.status === 'uploading' ? preview.progress + '%' : preview.status }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
  uploadUrl: {
    type: String,
    required: true
  },
  csrfToken: {
    type: String,
    required: true
  }
});

const emit = defineEmits(['upload-success', 'upload-error']);

const isDragging = ref(false);
const previews = ref([]);

const handleDrop = (e) => {
  isDragging.value = false;
  handleFiles(e.dataTransfer.files);
};

const handleFileInput = (e) => {
  handleFiles(e.target.files);
};

const handleFiles = (files) => {
  Array.from(files).forEach(file => {
    const fileId = Math.random().toString(36).substring(7);
    const previewObj = { id: fileId, src: null, file, progress: 0, status: 'waiting', url: null };
    
    // Preview
    if (file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = (e) => {
            previewObj.src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
    
    previews.value.push(previewObj);
    uploadFile(file, previewObj);
  });
};

const uploadFile = (file, previewObj) => {
  const formData = new FormData();
  formData.append('file', file);

  const xhr = new XMLHttpRequest();
  xhr.open('POST', props.uploadUrl, true);
  xhr.setRequestHeader('X-CSRF-TOKEN', props.csrfToken);
  xhr.setRequestHeader('Accept', 'application/json');

  xhr.upload.onprogress = (e) => {
    if (e.lengthComputable) {
       previewObj.progress = Math.round((e.loaded / e.total) * 100);
       previewObj.status = 'uploading';
    }
  };

  xhr.onload = () => {
    if (xhr.status === 200) {
        const data = JSON.parse(xhr.responseText);
        if (data.success) {
            previewObj.status = 'success';
            previewObj.url = data.url;
            emit('upload-success', data);
        } else {
            previewObj.status = 'error';
            emit('upload-error', data);
        }
    } else {
        previewObj.status = 'error';
        emit('upload-error', { message: 'HTTP Error' });
    }
  };

  xhr.onerror = () => {
    previewObj.status = 'error';
    emit('upload-error', { message: 'Network Error' });
  };

  xhr.send(formData);
};
</script>
