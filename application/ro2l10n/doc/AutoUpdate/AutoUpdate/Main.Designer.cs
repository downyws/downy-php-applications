namespace AutoUpdate
{
    partial class Main
    {
        /// <summary>
        /// 必需的设计器变量。
        /// </summary>
        private System.ComponentModel.IContainer components = null;

        /// <summary>
        /// 清理所有正在使用的资源。
        /// </summary>
        /// <param name="disposing">如果应释放托管资源，为 true；否则为 false。</param>
        protected override void Dispose(bool disposing)
        {
            if (disposing && (components != null))
            {
                components.Dispose();
            }
            base.Dispose(disposing);
        }

        #region Windows 窗体设计器生成的代码

        /// <summary>
        /// 设计器支持所需的方法 - 不要
        /// 使用代码编辑器修改此方法的内容。
        /// </summary>
        private void InitializeComponent()
        {
            this.components = new System.ComponentModel.Container();
            System.ComponentModel.ComponentResourceManager resources = new System.ComponentModel.ComponentResourceManager(typeof(Main));
            this.pic_1 = new System.Windows.Forms.PictureBox();
            this.lbl_1 = new System.Windows.Forms.Label();
            this.txt_main = new System.Windows.Forms.TextBox();
            this.lbl_2 = new System.Windows.Forms.Label();
            this.pb_1 = new System.Windows.Forms.ProgressBar();
            this.btn_cmd = new System.Windows.Forms.Button();
            this.lbl_3 = new System.Windows.Forms.Label();
            this.lbl_4 = new System.Windows.Forms.Label();
            this.llb_cmd = new System.Windows.Forms.LinkLabel();
            this.timer1 = new System.Windows.Forms.Timer(this.components);
            ((System.ComponentModel.ISupportInitialize)(this.pic_1)).BeginInit();
            this.SuspendLayout();
            // 
            // pic_1
            // 
            this.pic_1.Image = global::AutoUpdate.Properties.Resources.UpEASoft;
            this.pic_1.Location = new System.Drawing.Point(0, 0);
            this.pic_1.Name = "pic_1";
            this.pic_1.Size = new System.Drawing.Size(165, 319);
            this.pic_1.TabIndex = 0;
            this.pic_1.TabStop = false;
            // 
            // lbl_1
            // 
            this.lbl_1.AutoSize = true;
            this.lbl_1.Font = new System.Drawing.Font("Microsoft Sans Serif", 15F, System.Drawing.FontStyle.Bold, System.Drawing.GraphicsUnit.Point, ((byte)(134)));
            this.lbl_1.Location = new System.Drawing.Point(178, 10);
            this.lbl_1.Name = "lbl_1";
            this.lbl_1.Size = new System.Drawing.Size(264, 25);
            this.lbl_1.TabIndex = 1;
            this.lbl_1.Text = "欢迎使用汉化补丁更新程序";
            // 
            // txt_main
            // 
            this.txt_main.Location = new System.Drawing.Point(183, 103);
            this.txt_main.Multiline = true;
            this.txt_main.Name = "txt_main";
            this.txt_main.ReadOnly = true;
            this.txt_main.ScrollBars = System.Windows.Forms.ScrollBars.Vertical;
            this.txt_main.Size = new System.Drawing.Size(285, 137);
            this.txt_main.TabIndex = 2;
            // 
            // lbl_2
            // 
            this.lbl_2.AutoSize = true;
            this.lbl_2.Location = new System.Drawing.Point(180, 45);
            this.lbl_2.Name = "lbl_2";
            this.lbl_2.Size = new System.Drawing.Size(271, 13);
            this.lbl_2.TabIndex = 3;
            this.lbl_2.Text = "本程序将自动完成的汉化补丁更新工作，请稍后！";
            // 
            // pb_1
            // 
            this.pb_1.Location = new System.Drawing.Point(183, 245);
            this.pb_1.Name = "pb_1";
            this.pb_1.Size = new System.Drawing.Size(285, 23);
            this.pb_1.TabIndex = 4;
            // 
            // btn_cmd
            // 
            this.btn_cmd.Enabled = false;
            this.btn_cmd.Location = new System.Drawing.Point(393, 277);
            this.btn_cmd.Name = "btn_cmd";
            this.btn_cmd.Size = new System.Drawing.Size(75, 23);
            this.btn_cmd.TabIndex = 5;
            this.btn_cmd.Text = "退出";
            this.btn_cmd.UseVisualStyleBackColor = true;
            this.btn_cmd.Click += new System.EventHandler(this.btn_cmd_Click);
            // 
            // lbl_3
            // 
            this.lbl_3.AutoSize = true;
            this.lbl_3.Location = new System.Drawing.Point(180, 62);
            this.lbl_3.Name = "lbl_3";
            this.lbl_3.Size = new System.Drawing.Size(54, 13);
            this.lbl_3.TabIndex = 6;
            this.lbl_3.Text = "Loading...";
            // 
            // lbl_4
            // 
            this.lbl_4.AutoSize = true;
            this.lbl_4.Location = new System.Drawing.Point(180, 79);
            this.lbl_4.Name = "lbl_4";
            this.lbl_4.Size = new System.Drawing.Size(54, 13);
            this.lbl_4.TabIndex = 7;
            this.lbl_4.Text = "Loading...";
            // 
            // llb_cmd
            // 
            this.llb_cmd.AutoSize = true;
            this.llb_cmd.Location = new System.Drawing.Point(183, 282);
            this.llb_cmd.Name = "llb_cmd";
            this.llb_cmd.Size = new System.Drawing.Size(122, 13);
            this.llb_cmd.TabIndex = 8;
            this.llb_cmd.TabStop = true;
            this.llb_cmd.Text = "http://www.hotzeal.net/";
            this.llb_cmd.LinkClicked += new System.Windows.Forms.LinkLabelLinkClickedEventHandler(this.llb_cmd_LinkClicked);
            // 
            // timer1
            // 
            this.timer1.Tick += new System.EventHandler(this.timer1_Tick);
            // 
            // Main
            // 
            this.AutoScaleDimensions = new System.Drawing.SizeF(6F, 13F);
            this.AutoScaleMode = System.Windows.Forms.AutoScaleMode.Font;
            this.ClientSize = new System.Drawing.Size(484, 312);
            this.Controls.Add(this.llb_cmd);
            this.Controls.Add(this.lbl_4);
            this.Controls.Add(this.lbl_3);
            this.Controls.Add(this.btn_cmd);
            this.Controls.Add(this.pb_1);
            this.Controls.Add(this.lbl_2);
            this.Controls.Add(this.txt_main);
            this.Controls.Add(this.lbl_1);
            this.Controls.Add(this.pic_1);
            this.Icon = ((System.Drawing.Icon)(resources.GetObject("$this.Icon")));
            this.MaximizeBox = false;
            this.MaximumSize = new System.Drawing.Size(500, 350);
            this.MinimumSize = new System.Drawing.Size(500, 350);
            this.Name = "Main";
            this.Text = "Ragnarok Online 2";
            this.Load += new System.EventHandler(this.Main_Load);
            ((System.ComponentModel.ISupportInitialize)(this.pic_1)).EndInit();
            this.ResumeLayout(false);
            this.PerformLayout();

        }

        #endregion

        private System.Windows.Forms.PictureBox pic_1;
        private System.Windows.Forms.Label lbl_1;
        private System.Windows.Forms.TextBox txt_main;
        private System.Windows.Forms.Label lbl_2;
        private System.Windows.Forms.ProgressBar pb_1;
        private System.Windows.Forms.Button btn_cmd;
        private System.Windows.Forms.Label lbl_3;
        private System.Windows.Forms.Label lbl_4;
        private System.Windows.Forms.LinkLabel llb_cmd;
        private System.Windows.Forms.Timer timer1;
    }
}

