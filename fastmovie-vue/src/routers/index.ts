
import { ResponseCode } from "@/common/const";
import { $http } from "@/common/http";
import { useRefs, useUserStore, useWebConfigStore } from "@/stores";
import { createRouter, createWebHashHistory } from "vue-router";
import { useLogin } from "@/composables/useLogin";
const router = createRouter({
    history: createWebHashHistory(),
    routes: [
        {
            path: '/main',
            name: 'main',
            component: () => import("@/layouts/control.vue"),
            meta: {
                title: '主页',
            },
            redirect: '/',
            children: [
                {
                    path: '/',
                    name: 'index',
                    component: () => import("@/pages/index/index.vue"),
                    meta: {
                        title: '首页',
                        menu: 'index',
                    }
                },
                {
                    path: '/creative',
                    name: 'creative',
                    component: () => import("@/pages/creative/index.vue"),
                    meta: {
                        title: '创意圈',
                        menu: 'creative',
                    }
                },
                {
                    path: '/actor',
                    name: 'actor',
                    component: () => import("@/pages/actor/index.vue"),
                    meta: {
                        title: '演员库',
                        menu: 'actor',
                    }
                },
                {
                    path: '/voice',
                    name: 'voice',
                    component: () => import("@/pages/voice/index.vue"),
                    meta: {
                        title: '声音库',
                        menu: 'voice',
                    }
                },
                {
                    path: '/user',
                    name: 'user',
                    component: () => import("@/pages/user/index.vue"),
                    meta: {
                        title: '个人中心',
                        menu: 'user',
                    }
                },
                {
                    path: '/square',
                    name: 'square',
                    component: () => import("@/pages/works/square.vue"),
                    meta: {
                        title: '广场',
                        menu: 'square',
                    }
                },
                {
                    path: '/works/:drama_id',
                    name: 'works-detail',
                    component: () => import("@/pages/works/drama.vue"),
                    meta: {
                        title: '短剧详情',
                        menu: 'works',
                    },
                    beforeEnter: (to, _from, next) => {
                        const userStore = useUserStore();
                        if (to.params.drama_id && userStore.hasLogin()) {
                            next()
                        } else {
                            next({ name: 'works' })
                        }
                    },
                },
                {
                    path: '/play/:drama_id/:episode_id',
                    name: 'play-detail',
                    component: () => import("@/pages/play/index.vue"),
                    meta: {
                        title: '短剧播放',
                    },
                    beforeEnter: (to, _from, next) => {
                        if (to.params.drama_id && to.params.episode_id) {
                            next()
                        } else {
                            next({ name: 'index' })
                        }
                    },
                }
            ]
        },
        {
            path: '/points',
            name: 'points',
            component: () => import("@/pages/marketing/points.vue"),
            meta: {
                title: '积分商城',
                menu: 'points',
            }
        },
        {
            path: '/code',
            name: 'user-code',
            component: () => import("@/pages/user/code.vue"),
            meta: {
                title: '邀请码',
                menu: 'user',
            }
        },
        {
            path: '/vip',
            name: 'vip',
            component: () => import("@/pages/marketing/vip.vue"),
            meta: {
                title: '会员中心',
                menu: 'vip',
            }
        },
        {
            path: '/article/:article',
            name: 'article',
            component: () => import("@/pages/article/content.vue"),
            meta: {
                title: '文章详情',
                menu: 'article',
            }
        },
        {
            path: '/generate/:drama_id/:episode_id?',
            name: 'generate',
            component: () => import("@/pages/generate/index.vue"),
            meta: {
                title: '生成剧本',
            },
            redirect: (to) => {
                return to.params.drama_id && to.params.episode_id ? `/generate/drama/${to.params.drama_id}/${to.params.episode_id}` : '/';
            },
            children: [
                {
                    path: '/generate/drama/:drama_id/:episode_id?',
                    name: 'generate-drama',
                    component: () => import("@/pages/generate/drama/index.vue"),
                    meta: {
                        title: '剧本调整',
                    },
                    beforeEnter: (to, _from, next) => {
                        const userStore = useUserStore();
                        if (!to.params.drama_id || !to.params.episode_id || !userStore.hasLogin()) {
                            next({ name: 'index' })
                        } else {
                            next()
                        }
                    },
                },
                {
                    path: '/generate/actors/:drama_id/:episode_id?',
                    name: 'generate-actors',
                    component: () => import("@/pages/generate/actors/index.vue"),
                    meta: {
                        title: '剧本调整',
                    },
                    beforeEnter: (to, _from, next) => {
                        const userStore = useUserStore();
                        if (!to.params.drama_id || !to.params.episode_id || !userStore.hasLogin()) {
                            next({ name: 'index' })
                        } else {
                            next()
                        }
                    },
                },
                {
                    path: '/generate/props/:drama_id/:episode_id?',
                    name: 'generate-props',
                    component: () => import("@/pages/generate/props/index.vue"),
                    meta: {
                        title: '剧本调整',
                    },
                    beforeEnter: (to, _from, next) => {
                        const userStore = useUserStore();
                        if (!to.params.drama_id || !to.params.episode_id || !userStore.hasLogin()) {
                            next({ name: 'index' })
                        } else {
                            next()
                        }
                    },
                },
                {
                    path: '/generate/scene/:drama_id/:episode_id?',
                    name: 'generate-scene',
                    component: () => import("@/pages/generate/scene/index.vue"),
                    meta: {
                        title: '剧本调整',
                    },
                    beforeEnter: (to, _from, next) => {
                        const userStore = useUserStore();
                        if (!to.params.drama_id || !to.params.episode_id || !userStore.hasLogin()) {
                            next({ name: 'index' })
                        } else {
                            next()
                        }
                    },
                },
                {
                    path: '/generate/storyboard/:drama_id/:episode_id?',
                    name: 'generate-storyboard',
                    component: () => import("@/pages/generate/storyboard/index.vue"),
                    meta: {
                        title: '剧本调整',
                    },
                    beforeEnter: (to, _from, next) => {
                        const userStore = useUserStore();
                        if (!to.params.drama_id || !to.params.episode_id || !userStore.hasLogin()) {
                            next({ name: 'index' })
                        } else {
                            next()
                        }
                    },
                }
            ]
        }
    ],
})
const getWebConfig = () => {
    return new Promise((resolve, reject) => {
        $http.get('/app/control/api/Public/config').then((res: any) => {
            if (res.code === ResponseCode.SUCCESS) {
                const webConfigStore = useWebConfigStore();
                webConfigStore.setWebConfig(res.data as WebConfigInterface);
                resolve(res.data);
            } else {
                reject();
            }
        }).catch(() => {
            reject();
        });
    })
}
router.beforeEach(async (to, _from, next) => {
    const webConfigStore = useWebConfigStore();
    const { WEBCONFIG } = useRefs(webConfigStore);
    webConfigStore.initWebConfig();
    if (!WEBCONFIG.value?.web_name) {
        await getWebConfig().then(() => {
            console.log('初始化网站配置成功');
        }).catch(() => {
            console.error('初始化网站配置失败');
        });
    }
    const userStore = useUserStore();
    userStore.initUserInfo();
    const { USERINFO } = useRefs(userStore);
    const whiteList = ['index', 'article']
    if (!userStore.hasLogin() && !whiteList.includes(to.name as string)) {
        return next({ name: 'index' })
    }
    // 如果用户已登录但没有填写邀请码，打开邀请码弹窗（不阻止路由跳转）
    if (userStore.hasLogin() && !USERINFO.value?.activation_time && to.name !== 'user-code') {
        const { openIcode } = useLogin()
        // 延迟打开弹窗，确保路由跳转完成后再显示
        setTimeout(() => {
            openIcode()
        }, 100)
    }
    if (userStore.hasLogin() && USERINFO.value?.activation_time && to.name === 'user-code') {
        return next({ name: 'index' })
    }
    next()
})
router.afterEach((to, _from) => {
    const { WEBCONFIG } = useWebConfigStore();
    if (to.name == 'index') {
        globalThis.document.title = `${WEBCONFIG?.web_title}`;
        return;
    }
    let subTitle = '';
    if (WEBCONFIG.web_title) {
        subTitle = ` - ${WEBCONFIG.web_title}`;
    } else if (WEBCONFIG.web_name) {
        subTitle = ` - ${WEBCONFIG.web_name}`;
    }
    globalThis.document.title = `${to.meta?.title}${subTitle}`
})
export default router