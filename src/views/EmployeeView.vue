<script>
import { ref, onMounted } from "vue";
import axios from "axios";
import * as bootstrap from 'bootstrap';

export default {
    name: "EmployeeView",

    setup() {
        // --- State ---
        const employees = ref([]);
        const loading = ref(true);
        const error = ref(null);

        // Form state for Modal
        const isEditing = ref(false);
        const currentEmployee = ref({
            emp_id: "",
            full_name: "",
            department: "",
            salary: 0,
            active: 1
        });

        const employeeToDelete = ref(null);

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
        const fetchEmployees = async () => {
            loading.value = true;
            try {
                const response = await axios.get("http://localhost/ICT12267-Vue/api/employee.php");
                employees.value = Array.isArray(response.data) ? response.data : [];
            } catch (err) {
                error.value = err.response?.data?.error || err.message || "ไม่สามารถดึงข้อมูลพนักงานได้";
            } finally {
                loading.value = false;
            }
        };

        const openAddModal = () => {
            isEditing.value = false;
            currentEmployee.value = {
                emp_id: "",
                full_name: "",
                department: "",
                salary: 0,
                active: 1
            };
        };

        const openEditModal = (employee) => {
            isEditing.value = true;
            currentEmployee.value = { ...employee };
        };

        const saveEmployee = async () => {
            try {
                let response;
                if (isEditing.value) {
                    response = await axios.put("http://localhost/ICT12267-Vue/api/employee.php", currentEmployee.value);
                } else {
                    response = await axios.post("http://localhost/ICT12267-Vue/api/employee.php", currentEmployee.value);
                }

                const result = response.data;
                if (result.error) throw new Error(result.error);

                await fetchEmployees();
                showStatus(
                    isEditing.value ? "อัปเดตสำเร็จ" : "เพิ่มพนักงานสำเร็จ",
                    result.message || "ดำเนินการเรียบร้อยแล้ว",
                    false
                );
            } catch (err) {
                const errMsg = err.response?.data?.error || err.message;
                showStatus("เกิดข้อผิดพลาด", errMsg, true);
            }
        };

        const confirmDelete = (id) => {
            employeeToDelete.value = id;
        };

        const deleteEmployee = async () => {
            if (!employeeToDelete.value) return;
            try {
                const response = await axios.delete("http://localhost/ICT12267-Vue/api/employee.php", {
                    params: { id: employeeToDelete.value }
                });

                const result = response.data;
                if (result.error) throw new Error(result.error);

                await fetchEmployees();
                showStatus("ลบข้อมูลสำเร็จ", result.message || "ข้อมูลถูกลบเรียบร้อยแล้ว", false);
                employeeToDelete.value = null;
            } catch (err) {
                const errMsg = err.response?.data?.error || err.message;
                showStatus("เกิดข้อผิดพลาด", errMsg, true);
            }
        };

        onMounted(fetchEmployees);

        return {
            employees,
            loading,
            error,
            isEditing,
            currentEmployee,
            employeeToDelete,
            modalMessage,
            openAddModal,
            openEditModal,
            saveEmployee,
            confirmDelete,
            deleteEmployee,
            fetchEmployees,
            showStatus
        };
    }
};
</script>

<template>
    <div class="container py-5">
        <!-- Card same as before... -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-3">
                <h4 class="mb-0"><i class="bi bi-briefcase-fill me-2"></i>ระบบจัดการข้อมูลพนักงาน</h4>
                <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#employeeModal"
                    @click="openAddModal">
                    <i class="bi bi-plus-circle-fill me-1"></i> เพิ่มพนักงานใหม่
                </button>
            </div>
            <div class="card-body p-0">
                <div v-if="loading" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">กำลังโหลดข้อมูลพนักงาน...</p>
                </div>

                <div v-else-if="error" class="alert alert-danger m-3">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ error }}
                    <button class="btn btn-sm btn-outline-danger float-end" @click="fetchEmployees">ลองใหม่</button>
                </div>

                <div v-else class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">รหัสพนักงาน</th>
                                <th>ชื่อ - นามสกุล</th>
                                <th>แผนก / ฝ่าย</th>
                                <th>เงินเดือน</th>
                                <th class="text-center">สถานะ</th>
                                <th class="text-center">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="emp in employees" :key="emp.emp_id">
                                <td class="ps-4 fw-bold text-primary">{{ emp.emp_id }}</td>
                                <td class="fw-medium text-dark">{{ emp.full_name }}</td>
                                <td><span class="badge bg-info text-dark">{{ emp.department }}</span></td>
                                <td>{{ Number(emp.salary).toLocaleString() }} ฿</td>
                                <td class="text-center">
                                    <span :class="emp.active ? 'badge bg-success' : 'badge bg-danger'">
                                        {{ emp.active ? 'กำลังทำงาน' : 'พ้นสภาพ' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group shadow-sm">
                                        <button class="btn btn-outline-warning btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#employeeModal" @click="openEditModal(emp)">
                                            <i class="bi bi-pencil-square"></i> แก้ไข
                                        </button>
                                        <button class="btn btn-danger btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#deleteConfirmModal" @click="confirmDelete(emp.emp_id)">
                                            <i class="bi bi-trash-fill"></i> ลบ
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="employees.length === 0">
                                <td colspan="6" class="text-center py-5 text-muted">ไม่ได้พบข้อมูลพนักงานในระบบ</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white py-3 text-muted small">
                จำนวนพนักงานทั้งหมด {{ employees.length }} คน
            </div>
        </div>

        <!-- --- Modal: Add / Edit Employee --- -->
        <div class="modal fade" id="employeeModal" tabindex="-1" aria-labelledby="employeeModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header" :class="isEditing ? 'bg-warning' : 'bg-primary text-white'">
                        <h5 class="modal-title" id="employeeModalLabel">
                            <i class="bi me-2" :class="isEditing ? 'bi-pencil-square' : 'bi-plus-circle-fill'"></i>
                            {{ isEditing ? 'แก้ไขข้อมูลพนักงาน' : 'เพิ่มพนักงานใหม่' }}
                        </h5>
                        <button type="button" class="btn-close" :class="!isEditing ? 'btn-close-white' : ''"
                            data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form @submit.prevent="saveEmployee">
                        <div class="modal-body p-4">
                            <div class="row g-3">
                                <div class="col-12" v-if="!isEditing">
                                    <label class="form-label fw-semibold">รหัสพนักงาน</label>
                                    <input type="text" v-model="currentEmployee.emp_id" class="form-control" required
                                        placeholder="เช่น EMP001">
                                </div>
                                <div class="col-12" v-else>
                                    <label class="form-label fw-semibold">รหัสพนักงาน (ไม่สามารถแก้ไขได้)</label>
                                    <input type="text" :value="currentEmployee.emp_id" class="form-control bg-light"
                                        readonly>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">ชื่อ - นามสกุล</label>
                                    <input type="text" v-model="currentEmployee.full_name" class="form-control" required
                                        placeholder="กรอกชื่อและนามสกุล">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">แผนก / ฝ่าย</label>
                                    <select v-model="currentEmployee.department" class="form-control" required>
                                        <option value="" selected disabled>เลือกแผนก / ฝ่าย</option>
                                        <option value="IT">IT</option>
                                        <option value="บัญชี">บัญชี</option>
                                        <option value="การตลาด">การตลาด</option>
                                        <option value="ทรัพยากรบุคคล">ทรัพยากรบุคคล</option>
                                        <option value="การเงิน">การเงิน</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">เงินเดือน (฿)</label>
                                    <input type="number" v-model="currentEmployee.salary" class="form-control" required
                                        min="0">
                                </div>
                                <div class="col-12 mt-4">
                                    <div class="form-check form-switch p-3 bg-light rounded shadow-sm border">
                                        <input class="form-check-input ms-0 me-2" type="checkbox" id="activeStatus"
                                            v-model="currentEmployee.active" :true-value="1" :false-value="0">
                                        <label class="form-check-label fw-bold" for="activeStatus">
                                            สถานะการทำงาน:
                                            {{ currentEmployee.active ? 'กำลังทำงาน (Active)' : 'พ้นสภาพ (Inactive)' }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">ยกเลิก</button>
                            <button type="submit" class="btn btn-primary px-4 shadow-sm" data-bs-dismiss="modal">
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
                        <i class="bi bi-person-x-fill text-danger display-1 mb-3"></i>
                        <h4 class="mb-2">ยืนยันการลบ?</h4>
                        <p class="text-muted small">คุณกำลังจะลบข้อมูลของพนักงานท่านนี้ออกจากระบบ ข้อมูลจะหายถาวร</p>
                    </div>
                    <div class="modal-footer border-0 justify-content-center pb-4">
                        <button type="button" class="btn btn-light px-4 shadow-sm"
                            data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="button" class="btn btn-danger px-4 shadow-sm" @click="deleteEmployee"
                            data-bs-dismiss="modal">ลบข้อมูล</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- --- Modal: Status Notification --- -->
        <div class="modal fade" id="statusModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-sm modal-dialog-centered">
                <div class="modal-content border-0 shadow text-center">
                    <div class="modal-body p-4">
                        <i class="bi mb-3 display-3"
                            :class="modalMessage.isError ? 'bi-x-circle-fill text-danger' : 'bi-check-circle-fill text-success'"></i>
                        <h4 class="mb-2">{{ modalMessage.title }}</h4>
                        <p class="text-muted mb-0">{{ modalMessage.content }}</p>
                    </div>
                    <div class="modal-footer border-0 justify-content-center pb-4">
                        <button type="button" class="btn px-5 shadow-sm"
                            :class="modalMessage.isError ? 'btn-danger' : 'btn-success'"
                            data-bs-dismiss="modal">ตกลง</button>
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
}

.modal-content {
    border-radius: 15px;
}
</style>
