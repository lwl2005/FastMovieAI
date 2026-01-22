import { ClipExporter, ClipOptions } from '@/common/av-cliper/ComposerOptions';
export const useCompsite = (data: any) => {
    const w = 720;
    const h = 1280;
    const createFileWriter = (defaultName = 'output.mp4'): Promise<WritableStream> => {
        return new Promise((resolve, reject) => {
            (window as any).showSaveFilePicker({
                suggestedName: defaultName,
                types: [
                    {
                        description: 'MP4 Video',
                        accept: { 'video/mp4': ['.mp4'] },
                    },
                ],
            }).then((handle: any) => {
                handle.createWritable().then((writable: any) => {
                    resolve(writable);
                }).catch((err: any) => {
                    reject(err);
                });
            }).catch((err: any) => {
                reject(err);
            });
        });
    }
    const Tracks: any = {
        video: [],
        dialogues: [],
        dialogues_audio: [],
        narrations: [],
        narrations_audio: [],
    };
    const start = async (stream: WritableStream) => {
        const len = data.length;
        let offsetUs = 0;

        for (let i = 0; i < len; i++) {
            const item = data[i];
            let temp: ClipOptions;
            // 主轨道素材：不设置 offsetUs，让系统自动按顺序追加
            if (item.use_material_type === 'video') {
                temp = {
                    type: 'video',
                    stream: (await fetch(item.video)).body!,
                    // 主轨道不需要设置 offsetUs，会自动计算
                    offsetUs,
                    durationUs: item.duration * 1000,
                };
            } else {
                temp = {
                    type: 'image',
                    stream: (await fetch(item.image)).body!,
                    // 主轨道不需要设置 offsetUs，会自动计算
                    offsetUs,
                    durationUs: item.duration * 1000,
                };
            }
            Tracks.video.push(temp);

            // 收集对话字幕片段
            if (item.dialogues.length > 0) {
                for (const dialogue of item.dialogues) {
                    Tracks.dialogues.push({
                        text: dialogue.content,
                        startTimeUs: offsetUs + dialogue.start_time * 1000,
                        endTimeUs: offsetUs + dialogue.end_time * 1000,
                    });

                    if (dialogue.audio) {
                        const audioTemp: ClipOptions = {
                            type: 'audio',
                            stream: (await fetch(dialogue.audio)).body!,
                            trackId: 'dialogue_audio', // 对话音频轨道
                            offsetUs: offsetUs + dialogue.start_time * 1000,
                            // durationUs 不设置，使用音频的实际时长
                        };
                        Tracks.dialogues_audio.push(audioTemp);
                    }
                }
            }

            // 收集旁白字幕片段
            if (item.narration) {
                Tracks.narrations.push({
                    text: item.narration,
                    startTimeUs: offsetUs,
                    endTimeUs: offsetUs + item.duration * 1000,
                });

                if (item.narration_audio) {
                    const audioTemp: ClipOptions = {
                        type: 'audio',
                        stream: (await fetch(item.narration_audio)).body!,
                        trackId: 'narration_audio', // 旁白音频轨道
                        offsetUs: offsetUs,
                        // durationUs 不设置，使用音频的实际时长
                    };
                    Tracks.narrations_audio.push(audioTemp);
                }
            }
            offsetUs += item.duration * 1000;
        }
        const composer = new ClipExporter(w, h);
        composer.setTracks(Tracks);
        await composer.initialization();

        // 导出
        await composer.export(stream);

        // 释放
        composer.destroy();
    }
    const download = () => {
        createFileWriter().then((stream: WritableStream) => {
            start(stream);
        }).catch((err: any) => {
            console.info(err);
        });
    }
    download();
    return {};
}