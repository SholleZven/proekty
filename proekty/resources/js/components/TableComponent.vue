<template>
  <div class="container">
    <h1>Загрузка Excel и отображение данных</h1>

    <form @submit.prevent="uploadFile" class="upload">
      <input type="file" @change="handleFile" />
      <button type="submit">Загрузить</button>
    </form>

    <table v-if="products.length" class="table">
      <thead>
        <tr><th>ID</th><th>Название</th><th>Цена</th></tr>
      </thead>
      <tbody>
        <tr v-for="product in products" :key="product.id">
          <td>{{ product.id }}</td>
          <td>{{ product.name }}</td>
          <td>{{ product.price }}</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
const products = ref([]);
const file = ref(null);

const fetchProducts = async () => {
  const res = await fetch('/api/products');
  products.value = await res.json();
};

const handleFile = (e) => {
  file.value = e.target.files[0];
};

const uploadFile = async () => {
  if (!file.value) return;

  const formData = new FormData();
  formData.append('file', file.value);

  await fetch('/api/upload', {
    method: 'POST',
    body: formData,
  });

  await fetchProducts();
};

onMounted(fetchProducts);
</script>

<style scoped lang="scss">
.container {
  max-width: 800px;
  margin: 2rem auto;
  padding: 1rem;
}

.upload {
  display: flex;
  gap: 1rem;
  margin-bottom: 1rem;

  input[type="file"] {
    flex: 1;
  }
}

.table {
  width: 100%;
  border-collapse: collapse;
  th, td {
    padding: 0.5rem;
    border: 1px solid #ccc;
  }
}
</style>
