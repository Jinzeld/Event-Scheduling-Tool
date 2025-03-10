async function handleImageUpload(file) {
    const formData = new FormData();
    formData.append('image', file);

    const response = await fetch('https://cs-361-micro-a.vercel.app/', {
        method: 'POST',
        body: formData,
    });

    const data = await response.json();
    if (response.ok) {
        return data.url; // URL of the uploaded image
    } else {
        console.error('Image upload failed:', data.error);
        return null;
    }
}
