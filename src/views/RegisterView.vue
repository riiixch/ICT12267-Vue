<script>
import { ref, onMounted } from "vue";
import * as bootstrap from 'bootstrap';

export default {
    name: "RegisterView",

    setup() {
        const form = ref({
            firstName: "",
            lastName: "",
            phone: "",
            username: "",
            password: "",
            confirmPassword: ""
        });

        // --- Modal Instances ---
        let statusModal = null;

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

        const handleRegister = () => {
            // Validation
            if (form.value.password !== form.value.confirmPassword) {
                showStatus("ยืนยันรหัสผ่านไม่ตรงกัน", "กรุณาตรวจสอบว่ารหัสผ่านและยืนยันรหัสผ่านเหมือนกัน", true);
                return;
            }

            if (!form.value.firstName || !form.value.lastName || !form.value.phone || !form.value.username || !form.value.password) {
                showStatus("ข้อมูลไม่ครบถ้วน", "กรุณากรอกข้อมูลให้ครบทุกช่องก่อนดำเนินการต่อ", true);
                return;
            }

            // Success simulation (No API integration yet as requested)
            showStatus(
                "ลงทะเบียนสำเร็จ", 
                `ยินดีต้อนรับคุณ ${form.value.firstName}! ขณะนี้หน้าเพจยังไม่ได้เชื่อมต่อกับระบบฐานข้อมูล แต่คุณได้ออกแบบส่วนติดต่อผู้ใช้เรียบร้อยแล้ว`,
                false
            );

            // Clear form
            /*
            form.value = {
                firstName: "",
                lastName: "",
                phone: "",
                username: "",
                password: "",
                confirmPassword: ""
            };
            */
        };

        return {
            form,
            modalMessage,
            handleRegister
        };
    }
};
</script>

<template>
    <div class="register-container py-5 d-flex align-items-center justify-content-center min-vh-100 bg-light">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-7 col-lg-5">
                    <div class="card border-0 shadow-lg p-3">
                        <div class="card-body">
                            <div class="text-center mb-4">
                                <div class="icon-box bg-primary text-white mx-auto mb-3 shadow">
                                    <i class="bi bi-person-fill-add display-4"></i>
                                </div>
                                <h2 class="fw-bold">สร้างบัญชีผู้ใช้ใหม่</h2>
                                <p class="text-muted small">ร่วมเป็นสมาชิกกับเราเพื่อรับสิทธิประโยชน์มากมาย</p>
                            </div>

                            <form @submit.prevent="handleRegister">
                                <div class="row g-3">
                                    <div class="col-6">
                                        <label class="form-label fw-semibold mb-1 small">ชื่อ</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-person"></i></span>
                                            <input type="text" v-model="form.firstName" class="form-control border-start-0" placeholder="ชื่อจริง">
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label fw-semibold mb-1 small">นามสกุล</label>
                                        <input type="text" v-model="form.lastName" class="form-control" placeholder="นามสกุล">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold mb-1 small">เบอร์โทรศัพท์</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-telephone"></i></span>
                                            <input type="tel" v-model="form.phone" class="form-control border-start-0" placeholder="0XX-XXXXXXX">
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold mb-1 small">ชื่อผู้ใช้ (Username)</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-person-badge"></i></span>
                                            <input type="text" v-model="form.username" class="form-control border-start-0" placeholder="username">
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold mb-1 small">รหัสผ่าน</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-shield-lock"></i></span>
                                            <input type="password" v-model="form.password" class="form-control border-start-0" placeholder="********">
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold mb-1 small">ยืนยันรหัสผ่านอีกครั้ง</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-check2-circle"></i></span>
                                            <input type="password" v-model="form.confirmPassword" class="form-control border-start-0" placeholder="********">
                                        </div>
                                    </div>
                                    <div class="col-12 mt-4">
                                        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold shadow-sm">
                                            <i class="bi bi-check-circle me-1"></i> ลงทะเบียนตอนนี้
                                        </button>
                                    </div>
                                </div>
                            </form>

                            <div class="mt-4 text-center">
                                <p class="text-muted small">มีบัญชีอยู่แล้ว? <a href="#" class="text-decoration-none fw-bold">เข้าสู่ระบบ</a></p>
                            </div>
                        </div>
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
                        <p class="text-muted mb-0 small">{{ modalMessage.content }}</p>
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
.register-container {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}
.card {
    border-radius: 20px;
}
.icon-box {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}
.btn-primary {
    border-radius: 10px;
    background: linear-gradient(45deg, #0d6efd, #0dcaf0);
    border: none;
    transition: all 0.3s;
}
.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(13, 110, 253, 0.3);
}
.form-control:focus {
    box-shadow: none;
    border-color: #0d6efd;
}
.input-group-text {
    border-color: #dee2e6;
}
</style>
