import React, { useState, useCallback } from 'react';

const ReactUpload = ({ uploadUrl, csrfToken, onUploadSuccess }) => {
    const [isDragging, setIsDragging] = useState(false);
    const [previews, setPreviews] = useState([]);
    const [uploading, setUploading] = useState(false);

    const handleDragEnter = (e) => {
        e.preventDefault();
        e.stopPropagation();
        setIsDragging(true);
    };

    const handleDragLeave = (e) => {
        e.preventDefault();
        e.stopPropagation();
        setIsDragging(false);
    };

    const handleDragOver = (e) => {
        e.preventDefault();
        e.stopPropagation();
    };

    const handleDrop = (e) => {
        e.preventDefault();
        e.stopPropagation();
        setIsDragging(false);
        const files = [...e.dataTransfer.files];
        handleFiles(files);
    };

    const handleFileInput = (e) => {
        const files = [...e.target.files];
        handleFiles(files);
    };

    const handleFiles = (files) => {
        files.forEach(file => {
            const fileId = Math.random().toString(36).substring(7);
            // Preview
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    setPreviews(prev => [...prev, { id: fileId, src: e.target.result, file, progress: 0, status: 'waiting' }]);
                };
                reader.readAsDataURL(file);
            } else {
                setPreviews(prev => [...prev, { id: fileId, src: null, file, progress: 0, status: 'waiting' }]);
            }
            uploadFile(file, fileId);
        });
    };

    const uploadFile = (file, fileId) => {
        setUploading(true);
        const formData = new FormData();
        formData.append('file', file);

        const xhr = new XMLHttpRequest();
        xhr.open('POST', uploadUrl, true);
        xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
        xhr.setRequestHeader('Accept', 'application/json');

        xhr.upload.onprogress = (e) => {
            if (e.lengthComputable) {
                const percent = Math.round((e.loaded / e.total) * 100);
                setPreviews(prev => prev.map(p => p.id === fileId ? { ...p, progress: percent, status: 'uploading' } : p));
            }
        };

        xhr.onload = () => {
            if (xhr.status === 200) {
                const data = JSON.parse(xhr.responseText);
                if (data.success) {
                    setPreviews(prev => prev.map(p => p.id === fileId ? { ...p, status: 'success', url: data.url } : p));
                    if (onUploadSuccess) onUploadSuccess(data);
                } else {
                    setPreviews(prev => prev.map(p => p.id === fileId ? { ...p, status: 'error' } : p));
                }
            } else {
                setPreviews(prev => prev.map(p => p.id === fileId ? { ...p, status: 'error' } : p));
            }
            setUploading(false);
        };

        xhr.onerror = () => {
            setPreviews(prev => prev.map(p => p.id === fileId ? { ...p, status: 'error' } : p));
            setUploading(false);
        };

        xhr.send(formData);
    };

    return (
        <div
            className={`border-2 border-dashed p-6 rounded-lg text-center transition-colors duration-200 ${isDragging ? 'border-blue-500 bg-blue-50' : 'border-gray-300'}`}
            onDragEnter={handleDragEnter}
            onDragLeave={handleDragLeave}
            onDragOver={handleDragOver}
            onDrop={handleDrop}
        >
            <div className="space-y-2">
                <div className="text-sm text-gray-600">
                    <label className="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500">
                        <span>Upload a file</span>
                        <input type="file" className="sr-only" onChange={handleFileInput} multiple />
                    </label>
                    <span className="pl-1">or drag and drop</span>
                </div>
                <p className="text-xs text-gray-500">PNG, JPG, GIF up to 10MB</p>
            </div>

            {previews.length > 0 && (
                <div className="mt-4 space-y-3 text-left">
                    {previews.map((preview) => (
                        <div key={preview.id} className="flex items-center space-x-4 bg-white p-3 rounded-lg shadow-sm border">
                            {preview.src ? (
                                <img src={preview.src} alt="Preview" className="h-12 w-12 object-cover rounded-md" />
                            ) : (
                                <div className="h-12 w-12 bg-gray-100 rounded-md flex items-center justify-center text-gray-500 text-xs uppercase">{preview.file.name.split('.').pop()}</div>
                            )}
                            <div className="flex-1 min-w-0">
                                <p className="text-sm font-medium text-gray-900 truncate">{preview.file.name}</p>
                                <div className="w-full bg-gray-200 rounded-full h-2.5 mt-2">
                                    <div
                                        className={`h-2.5 rounded-full ${preview.status === 'success' ? 'bg-green-500' : (preview.status === 'error' ? 'bg-red-500' : 'bg-blue-600')}`}
                                        style={{ width: `${preview.progress}%` }}
                                    ></div>
                                </div>
                            </div>
                            <div className="text-xs text-gray-500">
                                {preview.status === 'success' ? (
                                    <a href={preview.url} target="_blank" rel="noreferrer" className="text-green-600 hover:underline">View</a>
                                ) : (
                                    <span>{preview.status === 'uploading' ? `${preview.progress}%` : preview.status}</span>
                                )}
                            </div>
                        </div>
                    ))}
                </div>
            )}
        </div>
    );
};

export default ReactUpload;
