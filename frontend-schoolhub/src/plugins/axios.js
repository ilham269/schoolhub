import axios from 'axios';

const apiClient = axios.create({
  // Sesuaikan dengan URL server backend Laravel kamu
  baseURL: 'http://localhost:8000/api', 
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json'
  }
});

export default apiClient;