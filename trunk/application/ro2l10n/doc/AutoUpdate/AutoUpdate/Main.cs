using System;
using System.Collections.Generic;
using System.IO;
using System.Text.RegularExpressions;
using System.Windows.Forms;
using System.Xml;

namespace AutoUpdate
{
    public partial class Main : Form
    {
        public Main()
        {
            InitializeComponent();
        }

        public string link_url = "http://www.hotzeal.net/";
        public string init_url = "http://ro2l10n.oa.hotzeal.net/index.php?a=autoupdate&m=info";
        public string log_path = Application.StartupPath + "/hotzeal/autoupdate/history.xml";

        public int step = 0;

        FileHelper fh;

        private void Main_Load(object sender, EventArgs e)
        {
            // 初始化界面
            this.lbl_3.Text = "";
            this.lbl_4.Text = "";
            this.WriteInfo("程序初始化...");
            this.timer1.Enabled = true;

            fh = new FileHelper(this.pb_1);
        }

        private void btn_cmd_Click(object sender, EventArgs e)
        {
            Application.Exit();
        }

        private void llb_cmd_LinkClicked(object sender, LinkLabelLinkClickedEventArgs e)
        {
            System.Diagnostics.Process.Start("iexplore.exe", this.link_url);
        }

        public void WriteInfo(string info)
        {
            this.txt_main.Text += info + "\r\n";
            this.txt_main.SelectionStart = this.txt_main.Text.Length;
            this.txt_main.ScrollToCaret();
        }

        private void timer1_Tick(object sender, EventArgs e)
        {
            switch (this.step)
            {
                case 0:
                    this.timer1.Enabled = false;
                    this.UpdatePatch();
                    break;
                default:
                    this.timer1.Enabled = false;
                    break;
            }
        }

        public void LogUpdate(int id, int update_time)
        {
            XmlDocument doc = new XmlDocument();

            if (File.Exists(this.log_path))
            {
                bool node_exists = false;
                doc.Load(this.log_path);
                XmlNodeList items = doc.SelectNodes("/files/file");
                foreach (XmlElement item in items)
                {
                    if (id == Convert.ToInt32(item.GetElementsByTagName("id")[0].InnerText))
                    {
                        node_exists = true;
                        item.GetElementsByTagName("update_time")[0].InnerText = update_time.ToString();
                    }
                }
                if (!node_exists)
                {
                    XmlNode xn = doc.SelectSingleNode("files");
                    XmlElement xe = doc.CreateElement("file");

                    XmlElement x_id = doc.CreateElement("id");
                    x_id.InnerText = id.ToString();
                    xe.AppendChild(x_id);

                    XmlElement x_update_time = doc.CreateElement("update_time");
                    x_update_time.InnerText = update_time.ToString();
                    xe.AppendChild(x_update_time);

                    xn.AppendChild(xe);
                }
            }
            else
            {
                doc.AppendChild(doc.CreateXmlDeclaration("1.0", "UTF-8", null));

                XmlElement el = doc.CreateElement("files");
                doc.AppendChild(el);

                XmlElement ec = doc.CreateElement("file");
                el.AppendChild(ec);

                XmlElement e_id = doc.CreateElement("id");
                e_id.InnerText = id.ToString();
                ec.AppendChild(e_id);

                XmlElement e_update_time = doc.CreateElement("update_time");
                e_update_time.InnerText = update_time.ToString();
                ec.AppendChild(e_update_time);
            }

            doc.Save(this.log_path);
        }

        public void UpdatePatch()
        {
            this.WriteInfo("获取服务器信息...");
            XmlDocument doc = new XmlDocument();
            string file_path = fh.Download(this.init_url);
            doc.Load(file_path);
            file_path = "";

            XmlNodeList items = doc.SelectNodes("/info/item");
            foreach (XmlElement item in items)
            {
                switch (item.GetElementsByTagName("key")[0].InnerText)
                {
                    case "lbl_3":
                        this.lbl_3.Text = item.GetElementsByTagName("value")[0].InnerText;
                        break;
                    case "lbl_4":
                        this.lbl_4.Text = item.GetElementsByTagName("value")[0].InnerText;
                        break;
                    case "update_url":
                        file_path = item.GetElementsByTagName("value")[0].InnerText;
                        break;
                }
            }
            if (file_path != "")
            {
                this.WriteInfo("获取更新列表...");
                file_path = fh.Download(file_path);

                this.WriteInfo("检查需要更新的文件...");
                // 整理历史更新记录
                Dictionary<int, int> history = new Dictionary<int, int>();
                if (File.Exists(this.log_path))
                {
                    doc.Load(this.log_path);
                    items = doc.SelectNodes("/files/file");
                    foreach (XmlElement item in items)
                    {
                        history.Add(
                            Convert.ToInt32(item.GetElementsByTagName("id")[0].InnerText),
                            Convert.ToInt32(item.GetElementsByTagName("update_time")[0].InnerText)
                        );
                    }
                }
                // 整理最新更新记录
                Dictionary<int, PatchFile> newest = new Dictionary<int, PatchFile>();
                doc.Load(file_path);
                items = doc.SelectNodes("/files/file");
                foreach (XmlElement item in items)
                {
                    newest.Add(
                        Convert.ToInt32(item.GetElementsByTagName("id")[0].InnerText)
                    , new PatchFile(
                        Convert.ToInt32(item.GetElementsByTagName("id")[0].InnerText),
                        Convert.ToInt32(item.GetElementsByTagName("update_time")[0].InnerText),
                        item.GetElementsByTagName("keyword")[0].InnerText,
                        item.GetElementsByTagName("path")[0].InnerText,
                        item.GetElementsByTagName("url")[0].InnerText
                    ));
                }
                // 取出补丁类型
                Dictionary<string, bool> patchs = new Dictionary<string, bool>();
                string[] files = Directory.GetFiles(Application.StartupPath + "/hotzeal/patch/");
                string temp_file_name = "";
                Regex reg = new Regex(@"^[0-9a-zA-Z]+\.md5$");
                foreach (var file in files)
                {
                    temp_file_name = Path.GetFileName(file);
                    if (reg.IsMatch(temp_file_name))
                    {
                        patchs.Add(temp_file_name.Replace(".md5", ""), true);
                    }
                }
                // 取出需要更新的记录
                List<PatchFile> updlist = new List<PatchFile>();
                bool isnewitem;
                foreach (var item in newest)
                {
                    if (!patchs.ContainsKey(item.Value.keyword))
                    {
                        continue;
                    }
                    isnewitem = true;
                    foreach (var _item in history)
                    {
                        if (_item.Key == item.Key)
                        {
                            isnewitem = false;
                            if (_item.Value < item.Value.update_time)
                            {
                                updlist.Add(item.Value);
                            }
                            break;
                        }
                    }
                    if (isnewitem)
                    {
                        updlist.Add(item.Value);
                    }
                }
                // 更新
                if (updlist.Count > 0)
                {
                    bool exist_err = false;
                    this.WriteInfo("开始更新文件...");
                    foreach (var item in updlist)
                    {
                        if (this.fh.UpdateFile(item.url, Application.StartupPath + "/" + item.path))
                        {
                            this.WriteInfo("文件 " + item.path + " 更新成功");
                            this.LogUpdate(item.id, item.update_time);
                        }
                        else
                        {
                            this.WriteInfo("文件 " + item.path + " 更新失败");
                            exist_err = true;
                        }
                    }
                    this.WriteInfo(exist_err ? "更新完成，部分文件更新失败，请重新尝试" : "更新完成");
                }
                else
                {
                    this.WriteInfo("没有需要更新的文件。");
                    this.btn_cmd.Enabled = true;
                }
            }
            else
            {
                this.WriteInfo("获取更新列表失败...");
                this.btn_cmd.Enabled = true;
            }
        }
    }

    public class PatchFile
    {
        public int id;
        public int update_time;
        public string keyword;
        public string path;
        public string url;

        public PatchFile(int _id, int _update_time, string _keyword, string _path, string _url)
        {
            this.id = _id;
            this.update_time = _update_time;
            this.keyword = _keyword;
            this.path = _path;
            this.url = _url;
        }
    }
}
