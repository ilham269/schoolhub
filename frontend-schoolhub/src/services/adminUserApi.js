import api from '@/utils/api'
export const adminUserApi={list:()=>api.get('/admin/users').then(r=>r.data),create:d=>api.post('/admin/users',d).then(r=>r.data),update:(id,d)=>api.put(`/admin/users/${id}`,d).then(r=>r.data),remove:id=>api.delete(`/admin/users/${id}`).then(r=>r.data),reset:(id,d)=>api.post(`/admin/users/${id}/reset-password`,d).then(r=>r.data)}
