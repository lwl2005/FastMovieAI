import { ElMessageBox } from "element-plus"
import type { ElMessageBoxOptions } from "element-plus"
import { h } from "vue"
import XLogin from "@/components/x-login/index.vue"
import XLIcode from "@/components/xl-icode/index.vue"
export const useLogin = () => {
    const open = () => {
        const options: ElMessageBoxOptions = {
            showClose: false,
            showCancelButton: false,
            showConfirmButton: false,
            customClass: 'x-login-message-box',
            closeOnPressEscape: false,
            closeOnClickModal: false,
            message: () => h(XLogin, {
                onSuccess: (e: any) => {
                    // @ts-ignore
                    options.onVanish?.()
                    if (e.data.activation_time == null || e.data.activation_time == '') {
                        // 打开邀请码
                        openIcode()
                    }
                },
                onClose: () => {
                    console.log('关闭登录')
                    // @ts-ignore
                    options.onVanish?.()
                }
            }),
        }
        ElMessageBox(options)
            .then(() => {
                console.log('打开登录成功')
            })
            .catch(() => {
                console.log('打开登录失败')
            })
    }
    const close = () => {
        console.log('关闭登录')
        ElMessageBox.close()
    }
    const openIcode = () => {
        const options: ElMessageBoxOptions = {
            showClose: false,
            showCancelButton: false,
            showConfirmButton: false,
            title: '填写邀请码',
            customStyle: {
                '--el-messagebox-width': 'min(586px,100%)',
                '--el-messagebox-border-radius':'20px',
                '--el-messagebox-padding-primary':'30px'
            },
            closeOnPressEscape: false,
            closeOnClickModal: false,
            message: () => h(XLIcode, {
                onSuccess: (userInfo: any) => {
                    // @ts-ignore
                    options.onVanish?.()
                    console.log('邀请码绑定成功', userInfo)
                },
                onClose: () => {
                    console.log('关闭邀请码')
                    // @ts-ignore
                    options.onVanish?.()
                }
            }),
        }
        ElMessageBox(options)
            .then(() => {
                console.log('打开邀请码成功')
            })
            .catch(() => {
                console.log('打开邀请码失败')
            })
    }
    return {
        open,
        close,
        openIcode
    }
}