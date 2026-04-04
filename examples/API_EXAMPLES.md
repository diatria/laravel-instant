# API Request Examples - Laravel Instant

This file contains practical examples of API requests using the Laravel Instant CRUD endpoints.

## Prerequisites

All examples assume:
- Base URL: `http://localhost:8000/api`
- Authentication: Bearer token (add `Authorization: Bearer YOUR_TOKEN` header if needed)

---

## 1. FIND - Get Single Product by ID

### Basic Request
```bash
curl -X GET "http://localhost:8000/api/products/1"
```

### With Relations
```bash
curl -X GET "http://localhost:8000/api/products/1?relations[]=category&relations[]=images"
```

### With Relations Count
```bash
curl -X GET "http://localhost:8000/api/products/1?relations[]=category&relations_count[]=reviews"
```

### Response
```json
{
  "code": "SUCCESS",
  "internal_code": 200,
  "message": "Data berhasil diambil dengan id: 1",
  "data": {
    "id": 1,
    "name": "Laptop Gaming",
    "sku": "LAP-001",
    "price": 15000000,
    "stock": 25,
    "category": {
      "id": 1,
      "name": "Electronics"
    }
  },
  "data_count": 0
}
```

---

## 2. ALL - Get All Products (No Pagination)

### Basic Request
```bash
curl -X GET "http://localhost:8000/api/products/all"
```

### With Relations
```bash
curl -X GET "http://localhost:8000/api/products/all?relations[]=category"
```

---

## 3. TABLE - Get Products with Pagination

### Basic Pagination
```bash
curl -X GET "http://localhost:8000/api/products/table?page=1&pagination_length=10"
```

### With Ordering
```bash
curl -X GET "http://localhost:8000/api/products/table?page=1&order=created_at:desc"
```

### Search by Name (LIKE)
```bash
curl -X GET "http://localhost:8000/api/products/table?queries[0][field]=name&queries[0][value]=Laptop&queries[0][strict]=false"
```

### Exact Match Filter
```bash
curl -X GET "http://localhost:8000/api/products/table?queries[0][field]=status&queries[0][value]=active&queries[0][strict]=true"
```

### Multiple Filters
```bash
curl -X GET "http://localhost:8000/api/products/table?\
queries[0][field]=status&queries[0][value]=active&queries[0][strict]=true&\
queries[1][field]=name&queries[1][value]=phone&queries[1][strict]=false"
```

### Complex Query with All Parameters
```bash
curl -X GET "http://localhost:8000/api/products/table?\
page=1&\
pagination_length=20&\
queries[0][field]=status&queries[0][value]=active&queries[0][strict]=true&\
queries[1][field]=price&queries[1][value]=1000000&queries[1][op]=ne&\
relations[]=category&\
relations_count[]=reviews&\
order=price:desc"
```

### Response (Paginated)
```json
{
  "code": "SUCCESS",
  "internal_code": 200,
  "message": "Data halaman 1 dari 50 berhasil diambil",
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "name": "Product 1",
        "price": 99.99
      }
    ],
    "first_page_url": "http://localhost:8000/products/table?page=1",
    "from": 1,
    "last_page": 5,
    "last_page_url": "http://localhost:8000/products/table?page=5",
    "next_page_url": "http://localhost:8000/products/table?page=2",
    "path": "http://localhost:8000/products/table",
    "per_page": 10,
    "prev_page_url": null,
    "to": 10,
    "total": 50
  }
}
```

---

## 4. CREATE - Insert New Product

### Basic Create
```bash
curl -X POST "http://localhost:8000/api/products" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "New Product",
    "sku": "PRD-001",
    "price": 99.99,
    "stock": 100,
    "category_id": 1,
    "status": "active"
  }'
```

### Create with Optional Fields
```bash
curl -X POST "http://localhost:8000/api/products" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Premium Laptop",
    "sku": "LAP-PRE-001",
    "price": 25000000,
    "stock": 10,
    "category_id": 1,
    "description": "High-end gaming laptop",
    "status": "active",
    "discount": 5
  }'
```

### Response
```json
{
  "code": "CREATED",
  "internal_code": 201,
  "message": "Data Product berhasil disimpan",
  "data": {
    "id": 10,
    "name": "New Product",
    "sku": "PRD-001",
    "price": 99.99,
    "stock": 100,
    "created_at": "2024-01-15T10:30:00.000000Z"
  }
}
```

---

## 5. UPDATE - Modify Existing Product

### Update Full Data
```bash
curl -X PUT "http://localhost:8000/api/products/1" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Updated Product Name",
    "price": 89.99,
    "stock": 50,
    "status": "active"
  }'
```

### Update Partial Data
```bash
curl -X PUT "http://localhost:8000/api/products/1" \
  -H "Content-Type: application/json" \
  -d '{
    "price": 79.99,
    "stock": 45
  }'
```

### Response
```json
{
  "code": "SUCCESS",
  "internal_code": 200,
  "message": "Data Product berhasil diperbaharui",
  "data": {
    "id": 1,
    "name": "Updated Product Name",
    "price": 89.99,
    "updated_at": "2024-01-15T11:30:00.000000Z"
  }
}
```

---

## 6. DELETE - Remove Products

### Delete Single Product
```bash
curl -X DELETE "http://localhost:8000/api/products" \
  -H "Content-Type: application/json" \
  -d '{
    "id": 1
  }'
```

### Delete Multiple Products
```bash
curl -X DELETE "http://localhost:8000/api/products" \
  -H "Content-Type: application/json" \
  -d '{
    "id": [1, 2, 3, 4, 5]
  }'
```

### Response
```json
{
  "code": "SUCCESS",
  "internal_code": 200,
  "message": "Deleted successfully"
}
```

---

## Advanced Query Examples

### 1. Get Active Products, Sorted by Price
```bash
curl -X GET "http://localhost:8000/api/products/table?\
queries[0][field]=status&queries[0][value]=active&queries[0][strict]=true&\
order=price:asc"
```

### 2. Search Products with Name Contains "phone"
```bash
curl -X GET "http://localhost:8000/api/products/table?\
queries[0][field]=name&queries[0][value]=phone&queries[0][strict]=false"
```

### 3. Get Products NOT in Status "inactive"
```bash
curl -X GET "http://localhost:8000/api/products/table?\
queries[0][field]=status&queries[0][value]=inactive&queries[0][op]=ne&queries[0][strict]=true"
```

### 4. Get Products in Multiple Categories (whereIn)
```bash
curl -X GET "http://localhost:8000/api/products/table" \
  -H "Content-Type: application/json" \
  -d '{
    "queries": [
      {
        "field": "category_id",
        "value": [1, 2, 3]
      }
    ]
  }'
```

### 5. Complex Business Query
Get active products, price above 100k, sorted by newest, with category relationship:
```bash
curl -X GET "http://localhost:8000/api/products/table?\
queries[0][field]=status&queries[0][value]=active&queries[0][strict]=true&\
queries[1][field]=price&queries[1][value]=100000&queries[1][strict]=true&\
relations[]=category&\
order=created_at:desc&\
pagination_length=20"
```

---

## JavaScript/Axios Examples

### Using Axios
```javascript
// Find product by ID
const product = await axios.get('/api/products/1', {
  params: {
    relations: ['category', 'images']
  }
});

// Get products table
const products = await axios.get('/api/products/table', {
  params: {
    page: 1,
    pagination_length: 10,
    queries: [
      { field: 'status', value: 'active', strict: true },
      { field: 'name', value: 'laptop', strict: false }
    ],
    relations: ['category'],
    order: 'created_at:desc'
  }
});

// Create product
const newProduct = await axios.post('/api/products', {
  name: 'New Product',
  sku: 'PRD-001',
  price: 99.99,
  stock: 100,
  category_id: 1,
  status: 'active'
});

// Update product
const updated = await axios.put('/api/products/1', {
  price: 89.99,
  stock: 50
});

// Delete products
await axios.delete('/api/products', {
  data: { id: [1, 2, 3] }
});
```

---

## Error Response Examples

### Validation Error
```json
{
  "code": "APPLICATION_ERROR",
  "internal_code": 500,
  "message": "The name field is required.",
  "data": null
}
```

### Not Found Error
```json
{
  "code": "NOT_FOUND",
  "internal_code": 404,
  "message": "Data tidak ditemukan",
  "data": null
}
```

### Permission Denied
```json
{
  "code": "FORBIDDEN",
  "internal_code": 403,
  "message": "You don't have permission to perform this action",
  "data": null
}
```

---

## Tips

1. **Always use `strict: false` for search/filter functionality** (uses LIKE query)
2. **Use `strict: true` for exact match** (dropdown filters, status, etc.)
3. **Combine multiple queries** for complex filtering
4. **Use `relations_count` for counting** without loading full data
5. **Always handle pagination** in table endpoints
6. **Order format**: `field:direction` (e.g., `created_at:desc`, `name:asc`)
