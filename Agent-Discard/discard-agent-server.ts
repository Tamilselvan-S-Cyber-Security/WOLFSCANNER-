import dns from 'node:dns';

dns.setDefaultResultOrder('ipv4first'); 
// ---------------------------

import express, { Express, Request, Response } from 'express';
import cors from 'cors';
import dotenv from 'dotenv';
import { Client, GatewayIntentBits, Partials, MessageFlags, Events } from 'discord.js';
import { supabase } from './config/supabase';
import Tesseract from 'tesseract.js';

// FIX: Standard require at the top level prevents pdf-parse's "Debug Mode" crash!
const pdfParse = require('pdf-parse');

dotenv.config();

const app: Express = express();
const port = process.env.PORT || 3000;

app.use(cors());
app.use(express.json());

// 1. INITIALIZE DISCORD CLIENT
const discordClient = new Client({
  intents: [
    GatewayIntentBits.Guilds,
    GatewayIntentBits.GuildMessages,
    GatewayIntentBits.MessageContent,
    GatewayIntentBits.DirectMessages,
  ],
  partials: [
    Partials.Channel,
  ],
});

discordClient.once(Events.ClientReady, () => {
  console.log(`Discord Bot is online as ${discordClient.user?.tag}`);
});

// 2. LISTEN FOR MESSAGES
discordClient.on('messageCreate', async (message) => {
  if (message.author.bot) return;

  if (message.content === '!ping') {
    await message.reply('Pong! Agent-Wolf is online.');
    return;
  }


  const isVoiceMessage = message.flags.has(MessageFlags.IsVoiceMessage);
  const audioAttachment = message.attachments.find(a => a.contentType?.startsWith('audio/'));

  if (isVoiceMessage || audioAttachment) {
    const statusMessage = await message.reply(' **Listening...**');
    await message.channel.sendTyping();

    try {
      const targetAudio = audioAttachment || message.attachments.first();
      if (!targetAudio) throw new Error("No audio found.");

      console.log('\n--- NEW VOICE REQUEST ---');
      console.log('1. Downloading audio from Discord...');
      const audioResponse = await fetch(targetAudio.url);
      const audioBuffer = await audioResponse.arrayBuffer();

      console.log('2. Sending audio to Hugging Face (NEW Router API)...');
      const whisperRes = await fetch('https://router.huggingface.co/hf-inference/models/openai/whisper-large-v3-turbo', {
        method: 'POST',
        headers: { 
          'Authorization': `Bearer ${process.env.HF_TOKEN}`,
          'Content-Type': targetAudio.contentType || 'audio/ogg' 
        },
        body: Buffer.from(audioBuffer)
      });

      if (!whisperRes.ok) {
        const errorText = await whisperRes.text();
        throw new Error(`Whisper API Failed: ${whisperRes.status} - ${errorText}`);
      }
      
      const whisperData = await whisperRes.json();
      const transcribedText = whisperData.text || whisperData[0]?.text;
      if (!transcribedText) throw new Error("Could not hear any words.");
      
      await statusMessage.edit(`🗣️ **You asked:** "${transcribedText}"\n **Thinking...**`);

      console.log(`3. Sending text to Qwen Brain: "${transcribedText}"`);
      const qwenRes = await fetch(`${process.env.NODE_1_URL}/api/generate`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          model: 'qwen2.5-coder',
          prompt: transcribedText,
          stream: false, 
        }),
      });

      const qwenData = await qwenRes.json();
      const aiReply = qwenData.response;
      await statusMessage.edit(`🗣️ **You asked:** "${transcribedText}"\n **Generating Voice Reply...**`);

      console.log('4. Generating audio via Google TTS Bypass...');
      const spokenReply = aiReply.length > 200 ? aiReply.substring(0, 197) + "..." : aiReply;
      
      const ttsUrl = `https://translate.google.com/translate_tts?ie=UTF-8&tl=en&client=tw-ob&q=${encodeURIComponent(spokenReply)}`;
      const ttsRes = await fetch(ttsUrl);

      if (!ttsRes.ok) throw new Error(`TTS API Failed: ${ttsRes.status} - Google rejected the request.`);
      
      const ttsBuffer = Buffer.from(await ttsRes.arrayBuffer());

      console.log('5. Uploading audio reply to Discord...');
      await statusMessage.edit({
        content: `🗣️ **You asked:** "${transcribedText}"\n **Agent-Wolf:** ${aiReply}`,
        files: [{ attachment: ttsBuffer, name: 'reply.mp3' }] 
      });
      
      console.log('--- SUCCESS ---\n');

    } catch (error: any) {
      console.error('Audio Pipeline Error:', error);
      const safeErrorMsg = error.message.length > 300 
        ? error.message.substring(0, 300) + "... [Error truncated to fit Discord]"
        : error.message;
      await statusMessage.edit(` **Audio Error:** ${safeErrorMsg}`);
    }
    return;
  }


  if (message.content.startsWith('!deepsearch ')) {
    const query = message.content.replace('!deepsearch ', '').trim();
    if (!query) return;

    await message.channel.sendTyping();
    const statusMessage = await message.reply(' **Initiating Deep Search Protocol...**');

    try {
      console.log(`\n[DEEP SEARCH] Querying Live Web (Stealth HTML Scraper) for: "${query}"`);
      
      const cheerio = require('cheerio');

      // 1. Spoof a real Windows 11 Chrome Browser to bypass DDG's bot detection
      const fetchResponse = await fetch(`https://html.duckduckgo.com/html/?q=${encodeURIComponent(query)}`, {
        headers: {
          'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36',
          'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
          'Accept-Language': 'en-US,en;q=0.5'
        }
      });

      if (!fetchResponse.ok) throw new Error(`Scraper blocked by target server: ${fetchResponse.status}`);

      const html = await fetchResponse.text();
      const $ = cheerio.load(html);

      let webContext = "[LIVE WEB SEARCH DATA:]\n";
      let resultCount = 0;

      // 2. Extract the data straight out of the HTML DOM
      $('.result').each((index: number, element: any) => {
        if (resultCount >= 5) return; // Keep it to the top 5 results
        
        const title = $(element).find('.result__title .result__a').text().trim();
        const snippet = $(element).find('.result__snippet').text().trim();
        
        // Clean up DDG's redirect URLs to get the actual source link
        let link = $(element).find('.result__url').attr('href') || "No URL";
        if (link.startsWith('//duckduckgo.com/l/?uddg=')) {
            const urlParams = new URLSearchParams(link.split('?')[1]);
            link = decodeURIComponent(urlParams.get('uddg') || link);
        }

        if (title && snippet) {
          webContext += `Result ${resultCount + 1}:\nTitle: ${title}\nSnippet: ${snippet}\nSource: ${link}\n\n`;
          resultCount++;
        }
      });

      if (resultCount === 0) {
        webContext += "No relevant information found on the web.\n";
        console.log('[DEBUG] Scraper failed to find HTML results. Page size:', html.length);
      }

      console.log(`[DEEP SEARCH] Found ${resultCount} results. Injecting into AI Brain...`);
      await statusMessage.edit('**Web data extracted. Synthesizing response...**');

      // 3. Create the specialized Deep Search Prompt (Now with Temporal Awareness)
      const currentTime = new Date().toLocaleString();
      const deepSearchPrompt = `[SYSTEM: You are Agent-Wolf. You are currently in DEEP SEARCH mode. 
      CURRENT SYSTEM TIME: ${currentTime}
      The user has asked a question that requires live internet data. I have scraped the web and provided the real-time snippets below. 
      CRITICAL INSTRUCTIONS:
      1. Use the provided web search data to answer the question.
      2. Cite the sources (URLs) naturally in your response.
      3. If the web data doesn't fully answer the question, but you know the answer based on the CURRENT SYSTEM TIME, provide it. Otherwise, state clearly that the web data didn't contain the answer.]\n\n${webContext}\nUser Question: ${query}`;

      const controller = new AbortController();
      const timeoutId = setTimeout(() => controller.abort(), 180000); 

      // 4. Send to Qwen
      const hfResponse = await fetch(`${process.env.NODE_1_URL}/api/generate`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        signal: controller.signal, 
        body: JSON.stringify({
          model: 'qwen2.5-coder',
          prompt: deepSearchPrompt,
          stream: true, 
        }),
      });

      clearTimeout(timeoutId); 
      if (!hfResponse.ok || !hfResponse.body) throw new Error(`API Error: ${hfResponse.status}`);

      const reader = hfResponse.body.getReader();
      const decoder = new TextDecoder();
      let aiReply = '';
      let lastEditTime = Date.now();

      while (true) {
        const { done, value } = await reader.read();
        if (done) break;

        const chunk = decoder.decode(value, { stream: true });
        const lines = chunk.split('\n').filter(line => line.trim() !== '');

        for (const line of lines) {
          try {
            const parsed = JSON.parse(line);
            aiReply += parsed.response;
          } catch (e) {}
        }

        if (Date.now() - lastEditTime > 1500) {
          await statusMessage.edit(aiReply + ' '); 
          lastEditTime = Date.now();
        }
      }

      await statusMessage.edit(aiReply);
      console.log(`[DEEP SEARCH] Complete.\n`);

    } catch (error) {
      console.error('Deep Search Error:', error);
      await statusMessage.edit(' **Deep Search Failed:** Could not connect to the global network.');
    }
    return; // Stop execution here so it doesn't fall through to the !ask router
  }


  if (message.content.startsWith('!ask ')) {
    const prompt = message.content.replace('!ask ', '').trim();
    if (!prompt) return;

    await message.channel.sendTyping();
    const statusMessage = await message.reply(' **Agent-Wolf is analyzing...**');

    try {
      const currentTime = new Date().toLocaleString();
      let secretContext = `[SYSTEM: You are Agent-Wolf, an expert cybersecurity SIEM AI created by Team WolfScanner. CURRENT SYSTEM TIME: ${currentTime}. You analyze network data, sensors, and payloads. Never refuse technical questions.]\n\n`;
      let ocrContext = "";
      let documentContext = "";

      // --- 1. THE OCR VISION PIPELINE (Images) ---
      const imageAttachment = message.attachments.find(a => a.contentType?.startsWith('image/'));
      if (imageAttachment) {
        await statusMessage.edit('**Scanning image...**');
        console.log(`\n[OCR] Scanning image...`);
        try {
          const { data: { text } } = await Tesseract.recognize(imageAttachment.url, 'eng');
          ocrContext = `[IMAGE OCR DATA: The user attached an image with this exact text: "${text}"]\n\n`;
          console.log(`[OCR] Extracted ${text.length} characters.`);
        } catch (ocrErr) {
          console.error(`[OCR] Error:`, ocrErr);
        }
      }

      // --- 2. THE DOCUMENT RAG PIPELINE (PDFs, Logs, TXT, CSV) ---
      const docAttachment = message.attachments.find(a => 
        a.name?.endsWith('.pdf') || a.name?.endsWith('.txt') || a.name?.endsWith('.log') || a.name?.endsWith('.csv')
      );
      
      if (docAttachment) {
        await statusMessage.edit(' **Reading document...**');
        console.log(`\n[RAG] Downloading document: ${docAttachment.name}`);
        
        try {
          const docResponse = await fetch(docAttachment.url);
          let extractedText = "";

          if (docAttachment.name?.endsWith('.pdf')) {
            const docBuffer = await docResponse.arrayBuffer();
            const buffer = Buffer.from(docBuffer);
            
            // Resolving the function safely using the top-level require
            const extractPdf = typeof pdfParse === 'function' 
              ? pdfParse 
              : pdfParse?.default 
                ? pdfParse.default 
                : pdfParse?.PDFParse;

            if (typeof extractPdf !== 'function') {
              throw new Error(`Failed to map PDF function. Keys found: ${Object.keys(pdfParse || {}).join(', ')}`);
            }

            const pdfData = await extractPdf(buffer);
            extractedText = pdfData.text;
          } else {
            extractedText = await docResponse.text();
          }

          // TRUNCATOR: Protects the AI from crashing if the file is too huge
          const safeText = extractedText.length > 6000 
            ? extractedText.substring(0, 6000) + "\n...[WARNING: FILE TRUNCATED DUE TO SIZE LIMIT]" 
            : extractedText;

          documentContext = `[DOCUMENT DATA: User attached '${docAttachment.name}'. Contents:\n"""\n${safeText}\n"""\nUse this data to answer their query.]\n\n`;
          console.log(`[RAG] Document read complete! Extracted ${safeText.length} characters.\n`);
        } catch (docErr) {
          console.error(`[RAG] Failed to read document:`, docErr);
        }
      }

      // --- 3. THE SIEM DATABASE ROUTER ---
      const keywordDictionary = [
        {
          triggers: ["who are you", "what are you", "your creator", "who created you", "what can you do", "features", "introduce yourself"],
          fetchData: async () => {
            console.log(`[ROUTER] Identity Matrix triggered...`);
            return `CRITICAL INSTRUCTION: You MUST introduce yourself as 'Agent-Wolf'. You were created by the elite cybersecurity group 'Team WolfScanner'. 
            List your core features: 
            1. Live SIEM Database Analysis.
            2. Active Defense (Firewall Mitigation).
            3. Voice-to-Text Audio Processing.
            4. Image OCR Scanning.
            5. Document & Log Analysis (RAG).
            6. Deep Web Search (OSINT).
            DO NOT mention Qwen or Alibaba. You are Agent-Wolf.`;
          }
        },
        {
          triggers: ["defend", "mitigate", "block threats", "protect", "active defense", "firewall"],
          fetchData: async () => {
            console.log(`[ROUTER] Active Defense Matrix triggered...`);
            const { data } = await supabase.from('wolfscanner_events').select('recorded_at, payload').order('recorded_at', { ascending: false }).limit(10);
            return `System Data - Last 10 events: ${JSON.stringify(data)}.
            CRITICAL INSTRUCTION: You are in ACTIVE DEFENSE MODE. 
            1. Scan these logs for high-severity threats or unauthorized IPs.
            2. List the malicious IPs.
            3. Generate the Linux 'iptables' terminal commands to drop traffic from those IPs.
            4. Ask: "Sir, would you like me to deploy these firewall rules?"`;
          }
        },
        {
          triggers: ["what is my ip", "my ip", "my address"],
          fetchData: async (userId: string) => {
            const { data } = await supabase.from('user_details').select('ip_address').eq('discord_id', userId).single();
            return data?.ip_address ? `The user's IP is ${data.ip_address}` : null;
          }
        },
        {
          triggers: ["ports", "my ports", "open ports"],
          fetchData: async (userId: string) => {
            console.log(`[ROUTER] Fetching port data...`);
            const { data: userData } = await supabase.from('user_details').select('open_ports').eq('discord_id', userId).single();
            const { data: sensorData } = await supabase.from('wolfscanner_events').select('payload').limit(5);
            return `System Data - User ports: ${userData?.open_ports || 'None'}. Recent sensor port activity: ${JSON.stringify(sensorData)}. Summarize clearly.`;
          }
        },
        {
          triggers: ["list of ip", "list of ips", "source ips", "ip addresses"],
          fetchData: async () => {
            console.log(`[ROUTER] Fetching IPs from sensor logs...`);
            const { data } = await supabase.from('wolfscanner_events').select('payload').order('recorded_at', { ascending: false }).limit(5);
            return `System Data - Recent payloads: ${JSON.stringify(data)}. Extract and list ONLY the IP addresses.`;
          }
        },
        {
          triggers: ["total payloads", "how many payloads", "true payloads", "payloads", "payload"],
          fetchData: async () => {
            console.log(`[ROUTER] Fetching payload counts...`);
            const { count } = await supabase.from('wolfscanner_events').select('*', { count: 'exact', head: true });
            const { data } = await supabase.from('wolfscanner_events').select('payload').order('recorded_at', { ascending: false }).limit(5);
            return `System Data - Total payloads in database: ${count}. Sample payloads: ${JSON.stringify(data)}. Tell the user the total count, and identify any payloads representing successful/true actions.`;
          }
        },
        {
          triggers: ["record", "records", "id", "event id", "fetch id"],
          fetchData: async () => {
            console.log(`[ROUTER] Fetching specific records and IDs...`);
            const { data } = await supabase.from('wolfscanner_events').select('source_event_id, recorded_at, payload').order('recorded_at', { ascending: false }).limit(5);
            return `System Data - Latest Records: ${JSON.stringify(data)}. List these records highlighting the 'source_event_id'.`;
          }
        },
        {
          triggers: ["suspicious activities", "suspicious", "malicious", "attacks", "threats"],
          fetchData: async () => {
            console.log(`[ROUTER] Fetching suspicious activities...`);
            const { data } = await supabase.from('wolfscanner_events').select('recorded_at, payload').order('recorded_at', { ascending: false }).limit(5);
            return `System Data - Last 5 events: ${JSON.stringify(data)}. Act as a SIEM analyst. ONLY report on 'suspicious activities' (high severity, attacks). Ignore normal traffic.`;
          }
        },
        {
          triggers: ["recent events", "sensor logs", "latest alerts"],
          fetchData: async () => {
            console.log(`[ROUTER] Fetching latest general sensor events...`);
            const { data, error } = await supabase.from('wolfscanner_events').select('recorded_at, payload').order('recorded_at', { ascending: false }).limit(3);
            if (error || !data || data.length === 0) return "No recent sensor events found.";
            return `LATEST SENSOR LOGS: ${JSON.stringify(data)}. Summarize these events clearly.`;
          }
        }
      ];

      const lowerPrompt = prompt.toLowerCase();
      const matchedEntry = keywordDictionary.find(entry => 
        entry.triggers.some(trigger => lowerPrompt.includes(trigger))
      );

      if (matchedEntry) {
        console.log(`\n[ROUTER] Keyword matched! Executing database fetch...`);
        try {
          const fetchedContext = await matchedEntry.fetchData(message.author.id);
          if (fetchedContext) {
            secretContext += `[DATABASE INJECTION: ${fetchedContext}]\n\n`;
            console.log(`[ROUTER] Success! Secret context injected into AI prompt.\n`);
          } else {
            console.log(`[ROUTER] Query ran, but no data was returned from Supabase.\n`);
          }
        } catch (dbError) {
          console.error(`[ROUTER] Database error:`, dbError);
        }
      }

      // 4. COMBINE THE MASTER PROMPT (System + OCR + RAG + Database + User Prompt)
      const finalPromptForAI = secretContext + ocrContext + documentContext + "User Question: " + prompt;

      const controller = new AbortController();
      const timeoutId = setTimeout(() => controller.abort(), 180000); 

      const hfResponse = await fetch(`${process.env.NODE_1_URL}/api/generate`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        signal: controller.signal, 
        body: JSON.stringify({
          model: 'qwen2.5-coder',
          prompt: finalPromptForAI,
          stream: true, 
        }),
      });

      clearTimeout(timeoutId); 

      if (!hfResponse.ok || !hfResponse.body) throw new Error(`API Error: ${hfResponse.status}`);

      const reader = hfResponse.body.getReader();
      const decoder = new TextDecoder();
      let aiReply = '';
      let lastEditTime = Date.now();
      let isFileMode = false;

      while (true) {
        const { done, value } = await reader.read();
        if (done) break;

        const chunk = decoder.decode(value, { stream: true });
        const lines = chunk.split('\n').filter(line => line.trim() !== '');

        for (const line of lines) {
          try {
            const parsed = JSON.parse(line);
            aiReply += parsed.response;
          } catch (e) {}
        }

        if (Date.now() - lastEditTime > 1500 && !isFileMode) {
          if (aiReply.length > 1900) {
            isFileMode = true;
            await statusMessage.edit(' **Writing massive script... preparing file download...**');
          } else {
            await statusMessage.edit(aiReply + ' '); 
          }
          lastEditTime = Date.now();
        }
      }

      if (aiReply.length > 1950 || isFileMode) {
        const buffer = Buffer.from(aiReply, 'utf-8');
        await statusMessage.edit({
          content: ' Analysis complete.',
          files: [{ attachment: buffer, name: 'response.md' }]
        });
      } else {
        await statusMessage.edit(aiReply);
      }

    } catch (error) {
      console.error('AI Routing Error:', error);
      await statusMessage.edit('❌ **Error:** Connection dropped. Check your Hub terminal.');
    }
  }
});

// 3. LOG THE BOT IN
discordClient.login(process.env.DISCORD_TOKEN);

// 4. START EXPRESS SERVER
app.get('/', (req: Request, res: Response) => {
  res.send('Welcome to the Nexus-AI Hub API');
});

app.listen(port, () => {
  console.log(` Nexus-AI Hub API is running on http://localhost:${port}`);
});