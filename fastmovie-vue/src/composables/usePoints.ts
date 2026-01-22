export const usePoints = (models: any[], num: Ref<number> = ref(1)) => {
    const points = computed(() => {
        let totalPoints = 0;
        for (const model of models) {
            if (model && model.value && model.value.id) {
                totalPoints += model.value.point || 0;
            }
        }
        totalPoints *= num.value;
        if (totalPoints === 0) {
            return '免费';
        }
        return totalPoints;
    })
    return points
}