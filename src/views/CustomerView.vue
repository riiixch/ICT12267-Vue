<script>
import { ref, onMounted } from "vue";
import axios from "axios";
import * as bootstrap from 'bootstrap';

export default {
    name: "CustomerView",

    setup() {
        // --- State ---
        const customers = ref([]);
        const loading = ref(true);
        const error = ref(null);

        // Form state for Modal
        const isEditing = ref(false);
        const currentCustomer = ref({
            customer_id: null,
            firstName: "",
            lastName: "",
            phone: "",
            username: "",
            password: ""
        });

        const customerToDelete = ref(null);

        // --- Modal Instances (to control programmatically) ---
        let statusModal = null;

        // Modal state
        const modalMessage = ref({
            title: "",
            content: "",
            isError: false
        });

        const showStatus = (title, content, isError = false) => {
            modalMessage.value = { title, content, isError };
            if (!statusModal) {
                statusModal = new bootstrap.Modal(document.getElementById('statusModal'));
            }
            statusModal.show();
        };

        // --- API Handlers ---
        const fetchCustomers = async () => {
            loading.value = true;
            try {
                const response = await axios.get("http://localhost/ICT12267-Vue/api/customer.php");
                customers.value = Array.isArray(response.data) ? response.data : [];
            } catch (err) {
                error.value = err.response?.data?.error || err.message || "ไม่สามารถดึงข้อมูลได้";
            } finally {
                loading.value = false;
            }
        };

        const openAddModal = () => {
            isEditing.value = false;
            currentCustomer.value = {
                customer_id: null,
                firstName: "",
                lastName: "",
                phone: "",
                username: "",
                password: ""
            };
        };

        const openEditModal = (customer) => {
            isEditing.value = true;
            currentCustomer.value = { ...customer };
        };

        const saveCustomer = async () => {
            try {
                let response;
                if (isEditing.value) {
                    response = await axios.put("http://localhost/ICT12267-Vue/api/customer.php", currentCustomer.value);
                } else {
                    response = await axios.post("http://localhost/ICT12267-Vue/api/customer.php", currentCustomer.value);
                }
                
                const result = response.data;
                if (result.error) throw new Error(result.error);
                
                await fetchCustomers();
                showStatus(
                    isEditing.value ? "อัปเดตสำเร็จ" : "เพิ่มข้อมูลสำเร็จ",
                    result.message || "ดำเนินการเรียบร้อยแล้ว",
                    false
                );
            } catch (err) {
                const errMsg = err.response?.data?.error || err.message;
                showStatus("เกิดข้อผิดพลาด", errMsg, true);
            }
        };

        const confirmDelete = (id) => {
            customerToDelete.value = id;
        };

        const deleteCustomer = async () => {
            if (!customerToDelete.value) return;
            try {
                const response = await axios.delete("http://localhost/ICT12267-Vue/api/customer.php", {
                    params: { id: customerToDelete.value }
                });
                
                const result = response.data;
                if (result.error) throw new Error(result.error);
                
                await fetchCustomers();
                showStatus("ลบข้อมูลสำเร็จ", result.message || "ข้อมูลถูกลบเรียบร้อยแล้ว", false);
                customerToDelete.value = null;
            } catch (err) {
                const errMsg = err.response?.data?.error || err.message;
                showStatus("เกิดข้อผิดพลาด", errMsg, true);
            }
        };

        onMounted(fetchCustomers);

        return {
            customers,
            loading,
            error,
            isEditing,
            currentCustomer,
            customerToDelete,
            modalMessage,
            openAddModal,
            openEditModal,
            saveCustomer,
            confirmDelete,
            deleteCustomer,
            fetchCustomers,
            showStatus
        };
    }
};
</script>

<template>
    <div class="container py-5">
        <!-- Card same as before... -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-3">
                <h4 class="mb-0"><i class="bi bi-people-fill me-2"></i>ระบบจัดการข้อมูลลูกค้า</h4>
                <button class="btn btn-light shadow-sm" data-bs-toggle="modal" data-bs-target="#customerModal" @click="openAddModal">
                    <i class="bi bi-person-plus-fill me-1"></i> เพิ่มลูกค้าใหม่
                </button>
            </div>
            <div class="card-body p-0">
                <div v-if="loading" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">กำลังโหลดข้อมูล...</p>
                </div>

                <div v-else-if="error" class="alert alert-danger m-3">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ error }}
                    <button class="btn btn-sm btn-outline-danger float-end" @click="fetchCustomers">ลองใหม่</button>
                </div>

                <div v-else class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">ลำดับ</th>
                                <th>รหัสลูกค้า</th>
                                <th>ชื่อ - นามสกุล</th>
                                <th>เบอร์โทรศัพท์</th>
                                <th>ชื่อผู้ใช้</th>
                                <th class="text-center">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(item, index) in customers" :key="item.customer_id">
                                <td class="ps-4 text-muted">{{ index + 1 }}</td>
                                <td><span class="badge bg-secondary opacity-75">{{ item.customer_id }}</span></td>
                                <td class="fw-medium text-dark">{{ item.firstName }} {{ item.lastName }}</td>
                                <td><i class="bi bi-telephone text-muted me-2"></i>{{ item.phone }}</td>
                                <td><i class="bi bi-person-circle text-muted me-2"></i>{{ item.username }}</td>
                                <td class="text-center">
                                    <div class="btn-group shadow-sm">
                                        <button class="btn btn-outline-warning btn-sm" data-bs-toggle="modal" data-bs-target="#customerModal" @click="openEditModal(item)">
                                            <i class="bi bi-pencil-square"></i> แก้ไข
                                        </button>
                                        <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteConfirmModal" @click="confirmDelete(item.customer_id)">
                                            <i class="bi bi-trash-fill"></i> ลบ
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="customers.length === 0">
                                <td colspan="6" class="text-center py-5 text-muted italic">ไม่มีข้อมูลลูกค้าในระบบ</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white py-3 text-muted small">
                แสดงข้อมูลลูกค้าทั้งหมด {{ customers.length }} รายการ
            </div>
        </div>

        <!-- --- Modal: Add / Edit Customer --- -->
        <div class="modal fade" id="customerModal" tabindex="-1" aria-labelledby="customerModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header" :class="isEditing ? 'bg-warning text-dark' : 'bg-success text-white'">
                        <h5 class="modal-title" id="customerModalLabel">
                            <i class="bi me-2" :class="isEditing ? 'bi-pencil-square' : 'bi-person-plus-fill'"></i>
                            {{ isEditing ? 'แก้ไขข้อมูลลูกค้า' : 'เพิ่มข้อมูลลูกค้าใหม่' }}
                        </h5>
                        <button type="button" class="btn-close" :class="!isEditing ? 'btn-close-white' : ''" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form @submit.prevent="saveCustomer">
                        <div class="modal-body p-4">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">ชื่อ</label>
                                    <input type="text" v-model="currentCustomer.firstName" class="form-control" required placeholder="กรอกชื่อ">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">นามสกุล</label>
                                    <input type="text" v-model="currentCustomer.lastName" class="form-control" required placeholder="กรอกนามสกุล">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">เบอร์โทรศัพท์</label>
                                    <input type="text" v-model="currentCustomer.phone" class="form-control" required placeholder="0XX-XXXXXXX">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">ชื่อผู้ใช้ (Username)</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-at"></i></span>
                                        <input type="text" v-model="currentCustomer.username" class="form-control" required placeholder="username">
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">รหัสผ่าน (Password)</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-key"></i></span>
                                        <input type="password" v-model="currentCustomer.password" class="form-control" required placeholder="********">
                                    </div>
                                    <div class="form-text text-muted" v-if="isEditing">กรุณากรอกรหัสผ่านเดิมหรือรหัสผ่านใหม่เพื่อยืนยันการเปลี่ยนแปลง</div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">ยกเลิก</button>
                            <button type="submit" class="btn px-4 shadow-sm" :class="isEditing ? 'btn-warning text-dark' : 'btn-success'" data-bs-dismiss="modal">
                                {{ isEditing ? 'บันทึกการแก้ไข' : 'บันทึกข้อมูล' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- --- Modal: Delete Confirmation --- -->
        <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-sm modal-dialog-centered">
                <div class="modal-content border-0 shadow text-center">
                    <div class="modal-body p-4">
                        <i class="bi bi-exclamation-circle text-danger display-1 mb-3"></i>
                        <h4 class="mb-2">ยืนยันการลบ?</h4>
                        <p class="text-muted">คุณแน่ใจหรือไม่ว่าต้องการลบข้อมูลลูกค้านี้? ข้อมูลจะถูกลบถาวร</p>
                    </div>
                    <div class="modal-footer border-0 justify-content-center pb-4">
                        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="button" class="btn btn-danger px-4 shadow-sm" @click="deleteCustomer" data-bs-dismiss="modal">ลบข้อมูล</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- --- Modal: Status Notification --- -->
        <div class="modal fade" id="statusModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-sm modal-dialog-centered">
                <div class="modal-content border-0 shadow text-center">
                    <div class="modal-body p-4">
                        <i class="bi mb-3 display-3" :class="modalMessage.isError ? 'bi-x-circle-fill text-danger' : 'bi-check-circle-fill text-success'"></i>
                        <h4 class="mb-2">{{ modalMessage.title }}</h4>
                        <p class="text-muted mb-0">{{ modalMessage.content }}</p>
                    </div>
                    <div class="modal-footer border-0 justify-content-center pb-4">
                        <button type="button" class="btn px-5 shadow-sm" :class="modalMessage.isError ? 'btn-danger' : 'btn-success'" data-bs-dismiss="modal">ตกลง</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.card {
    border-radius: 12px;
}
.card-header {
    border-radius: 12px 12px 0 0 !important;
}
.table thead th {
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.85rem;
    letter-spacing: 0.5px;
}
.btn {
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.2s;
}
.btn-group .btn {
    border-radius: 6px;
}
.form-control, .input-group-text {
    border-radius: 8px;
}
.modal-content {
    border-radius: 15px;
}
</style>