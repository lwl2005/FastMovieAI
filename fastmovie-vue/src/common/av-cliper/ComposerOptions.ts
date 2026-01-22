import {
    Combinator,
    AudioClip,
    EmbedSubtitlesClip,
    ImgClip,
    MP4Clip,
    renderTxt2ImgBitmap,
    OffscreenSprite,
} from '@webav/av-cliper';

type ClipType = 'video' | 'audio' | 'image' | 'text';
/**
 * 将时间（微秒）转换为 SRT 时间格式 (HH:MM:SS,mmm)
 */
function formatSrtTime(us: number): string {
    const totalMs = Math.floor(us / 1000);
    const hours = Math.floor(totalMs / 3600000);
    const minutes = Math.floor((totalMs % 3600000) / 60000);
    const seconds = Math.floor((totalMs % 60000) / 1000);
    const milliseconds = totalMs % 1000;
    return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')},${String(milliseconds).padStart(3, '0')}`;
}


/**
 * 将单个文本片段转换为 SRT 格式
 * @param text 文本内容
 * @param startTimeUs 开始时间（微秒）
 * @param endTimeUs 结束时间（微秒）
 * @param index 字幕序号（从1开始）
 * @returns SRT 格式的字符串
 */
export function textToSrt(
    text: string,
    startTimeUs: number,
    endTimeUs: number,
    index = 1,
): string {
    return `${index}\n${formatSrtTime(startTimeUs)} --> ${formatSrtTime(endTimeUs)}\n${text}\n\n`;
}

export interface SubtitleStyle {
    color?: string;
    textBgColor?: string | null;
    type?: 'srt';
    fontFamily?: string;
    fontSize?: number;
    letterSpacing?: string | null;
    bottomOffset?: number;
    strokeStyle?: string;
    lineWidth?: number | null;
    lineCap?: CanvasLineCap | null;
    lineJoin?: CanvasLineJoin | null;
    textShadow?: {
        offsetX: number;
        offsetY: number;
        blur: number;
        color: string;
    };
    videoWidth: number;
    videoHeight: number;
    fontWeight?: string | number;
    fontStyle?: 'normal' | 'italic';
}

export interface ClipOptions {
    type: ClipType;
    stream?: ReadableStream;
    text?: string;
    style?: string;
    // 字幕相关配置
    subtitleSrt?: string; // SRT 格式的字幕内容
    subtitleStyle?: SubtitleStyle; // 字幕样式配置
    durationUs?: number; // 可选，默认用素材本身长度
    trackId?: string | number; // 轨道ID，默认为 'main'（主轨道）
    offsetUs?: number; // 插入时间（微秒），非主轨道可以使用此参数指定插入时间
}

export class ClipExporter {
    private combinator: Combinator;
    private tracks: any;
    private width: number;
    private height: number;
    private durationUs: number = 0;
    constructor(width = 1280, height = 720, bgColor = '#000') {
        this.width = width;
        this.height = height;
        this.combinator = new Combinator({
            width,
            height,
            bgColor,
        });
    }
    setTracks(tracks: any) {
        this.tracks = tracks;
        return this;
    }
    async initializationDialogues() {
        const dialogueSubtitles: any[] = [];
        for (const track of this.tracks.dialogues) {
            dialogueSubtitles.push({
                text: track.text,
                start: track.startTimeUs,
                end: track.endTimeUs,
            });
        }
        const clip = new EmbedSubtitlesClip(dialogueSubtitles, {
            videoWidth: this.width,
            videoHeight: this.height,
            fontSize: 24,
            fontFamily: 'Noto Sans SC',
            strokeStyle: '#000',
            lineWidth: 2,
            lineJoin: 'round',
            lineCap: 'round',
            bottomOffset: 10,
        });
        const spr = new OffscreenSprite(clip);
        spr.time.offset = 0;
        spr.time.duration = this.durationUs;
        await this.combinator.addSprite(spr);
    }
    private narrationStyle = `
    font-size: 20px; 
  font-weight:600;
    color: white;
    -webkit-text-stroke: 1px #000;
    writing-mode: vertical-lr;
  text-orientation: upright;
  white-space:wrap;
  padding:10px;`;
    async initializationNarrations() {
        this.narrationStyle = `
        height:${this.height / 2}px;
        ${this.narrationStyle}`;
        for (const track of this.tracks.narrations) {
            const clip = new ImgClip(
                await renderTxt2ImgBitmap(
                    track.text,
                    this.narrationStyle,
                ),
            );
            await clip.ready;
            const spr = new OffscreenSprite(clip);
            spr.time.offset = track.startTimeUs;
            spr.time.duration = track.endTimeUs - track.startTimeUs;
            await this.combinator.addSprite(spr);
        }
    }
    async initialization() {
        let spr: OffscreenSprite;
        for (const track of this.tracks.video) {
            if (track.type === 'video') {
                spr = new OffscreenSprite(
                    new MP4Clip(track.stream),
                );
                spr.time.offset = track.offsetUs;
                spr.time.duration = track.durationUs;
            } else {
                spr = new OffscreenSprite(
                    new ImgClip(track.stream),
                );
                spr.time.offset = track.offsetUs;
                spr.time.duration = track.durationUs;
            }
            this.durationUs += track.durationUs;
            await this.combinator.addSprite(spr);
        }
        for (const track of this.tracks.dialogues_audio) {
            const clip = new AudioClip(track.stream);
            await clip.ready;
            spr = new OffscreenSprite(clip);
            spr.time.offset = track.offsetUs;
            spr.time.duration = clip.meta.duration;
            await this.combinator.addSprite(spr);
        }
        for (const track of this.tracks.narrations_audio) {
            const clip = new AudioClip(track.stream);
            await clip.ready;
            spr = new OffscreenSprite(clip);
            spr.time.offset = track.offsetUs;
            spr.time.duration = clip.meta.duration;
            await this.combinator.addSprite(spr);
        }
        await this.initializationDialogues();
        await this.initializationNarrations();
    }

    async export(writer: WritableStream) {
        this.combinator.on('OutputProgress', (progress: number) => {
            console.log('OutputProgress', progress);
        });
        await this.combinator.output().pipeTo(writer);
    }

    destroy() {
        this.combinator.destroy();
    }
}
