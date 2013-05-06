using System;
using System.Collections.Generic;
using System.Data.Entity;
using System.Linq;
using System.Web;

namespace everything.Models
{
    public class Product
    {
        public int Id { get; set; }
        public string Name { get; set; }
        public string ShortName { get; set; }
        public string Category { get; set; }
        public decimal Price { get; set; }
        public string About { get; set; }
    }

    public class ProductsDbContext : DbContext
    {
        public DbSet<Product> Products { get; set; }
    }
}