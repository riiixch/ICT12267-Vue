<script>
import { ref } from "vue";
import * as bootstrap from 'bootstrap';
import axios from 'axios';

export default {
    name: "LoginView",

    setup() {
        const form = ref({
            username: "",
            password: ""
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

        const handleLogin = async () => {
            if (!form.value.username || !form.value.password) {
                showStatus("ข้อมูลไม่ครบถ้วน", "กรุณากรอกชื่อผู้ใช้และรหัสผ่านก่อนดำเนินการต่อ", true);
                return;
            }

            try {
                const response = await axios.post('http://localhost/ICT12267-Vue/api/login.php', {
                    username: form.value.username,
                    password: form.value.password
                });

                if (response.data.success) {
                    showStatus("เข้าสู่ระบบสำเร็จ", response.data.message, false);
                    // ในระบบจริงอาจมีการนำทางไปหน้าอื่น เช่น:
                    // router.push('/');
                } else {
                    showStatus("เข้าสู่ระบบไม่สำเร็จ", response.data.error || "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง", true);
                }
            } catch (error) {
                const errorMsg = error.response?.data?.error || "ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้";
                showStatus("เกิดข้อผิดพลาด", errorMsg, true);
            }
        };

        return {
            form,
            modalMessage,
            handleLogin
        };
    }
};
</script>

<template>
    <div class="login-container py-5 d-flex align-items-center justify-content-center min-vh-100 bg-light">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-4">
                    <div class="card border-0 shadow-lg p-3">
                        <div class="card-body">
                            <div class="text-center mb-4">
                                <div class="icon-box bg-primary text-white mx-auto mb-3 shadow">
                                    <i class="bi bi-person-circle display-4"></i>
                                </div>
                                <h2 class="fw-bold">เข้าสู่ระบบ</h2>
                                <p class="text-muted small">กรุณากรอกข้อมูลเพื่อเข้าใช้งานระบบ</p>
                            </div>

                            <form @submit.prevent="handleLogin">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label fw-semibold mb-1 small">ชื่อผู้ใช้ (Username)</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white border-end-0"><i
                                                    class="bi bi-person"></i></span>
                                            <input type="text" v-model="form.username"
                                                class="form-control border-start-0" placeholder="username">
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold mb-1 small">รหัสผ่าน</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white border-end-0"><i
                                                    class="bi bi-shield-lock"></i></span>
                                            <input type="password" v-model="form.password"
                                                class="form-control border-start-0" placeholder="********">
                                        </div>
                                    </div>
                                    <div class="col-12 mt-4">
                                        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold shadow-sm">
                                            <i class="bi bi-box-arrow-in-right me-1"></i> เข้าสู่ระบบ
                                        </button>
                                    </div>
                                </div>
                            </form>

                            <div class="mt-4 text-center">
                                <p class="text-muted small">ยังไม่มีบัญชี? <router-link to="/register"
                                        class="text-decoration-none fw-bold">ลงทะเบียนใหม่</router-link></p>
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
                        <i class="bi mb-3 display-3"
                            :class="modalMessage.isError ? 'bi-x-circle-fill text-danger' : 'bi-check-circle-fill text-success'"></i>
                        <h4 class="mb-2">{{ modalMessage.title }}</h4>
                        <p class="text-muted mb-0 small">{{ modalMessage.content }}</p>
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
.login-container {
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
