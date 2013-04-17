using System;
using System.Collections.Generic;
using System.Data.Entity;
using System.Linq;
using System.Web;

namespace everything.Models
{
    public class Article
    {
        public int Id { get; set; }
        public string Name { get; set; }
        public string Category { get; set; }
        public string Text { get; set; }
        public string LinkToFile { get; set; }
    }

    public class ArticlesDBContext : DbContext
    {
        public DbSet<Article> Articles { get; set; }
    }
}